<?php

namespace Clue\React\Socks;

use React\Promise\Deferred;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\Resolver;
use React\Stream\Stream;
use React\EventLoop\LoopInterface;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\DnsConnector;
use React\SocketClient\TcpConnector;
use React\SocketClient\SecureConnector;
use Clue\React\Socks\Connector;
use \Exception;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Client
{
    /**
     *
     * @var ConnectorInterface
     */
    private $connector;

    /**
     *
     * @var Resolver
     */
    private $resolver;

    private $socksHost;

    private $socksPort;

    private $timeout;

    /**
     * @var LoopInterface
     */
    protected $loop;

    private $resolveLocal = true;

    private $protocolVersion = null;

    protected $auth = null;

    public function __construct($socksUri, LoopInterface $loop, ConnectorInterface $connector = null, Resolver $resolver = null)
    {
        // assume default scheme if none is given
        if (strpos($socksUri, '://') === false) {
            $socksUri = 'socks://' . $socksUri;
        }

        // parse URI into individual parts
        $parts = parse_url($socksUri);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException('Invalid SOCKS server URI "' . $socksUri . '"');
        }

        // assume default port
        if (!isset($parts['port'])) {
            $parts['port'] = 1080;
        }

        if ($resolver === null) {
            // default to using Google's public DNS server
            $dnsResolverFactory = new DnsFactory();
            $resolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);
        }
        if ($connector === null) {
            $connector = new DnsConnector(new TcpConnector($loop), $resolver);
        }

        $this->loop = $loop;
        $this->socksHost = $parts['host'];
        $this->socksPort = $parts['port'];
        $this->connector = $connector;
        $this->resolver = $resolver;

        $this->timeout = ini_get("default_socket_timeout");
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setResolveLocal($resolveLocal)
    {
        if ($this->protocolVersion === '4' && !$resolveLocal) {
            throw new UnexpectedValueException('SOCKS4 requires resolving locally. Consider using another protocol version or resolving locally');
        }
        $this->resolveLocal = $resolveLocal;
    }

    public function setProtocolVersion($version)
    {
        if ($version !== null) {
            $version = (string)$version;
            if (!in_array($version, array('4', '4a', '5'), true)) {
                throw new InvalidArgumentException('Invalid protocol version given');
            }
            if ($version !== '5' && $this->auth){
                throw new UnexpectedValueException('Unable to change protocol version to anything but SOCKS5 while authentication is used. Consider removing authentication info or sticking to SOCKS5');
            }
            if ($version === '4' && !$this->resolveLocal) {
                throw new UnexpectedValueException('Unable to change to SOCKS4 while resolving locally is turned off. Consider using another protocol version or resolving locally');
            }
        }
        $this->protocolVersion = $version;
    }

    /**
     * set login data for username/password authentication method (RFC1929)
     *
     * @param string $username
     * @param string $password
     * @link http://tools.ietf.org/html/rfc1929
     */
    public function setAuth($username, $password)
    {
        if (strlen($username) > 255 || strlen($password) > 255) {
            throw new InvalidArgumentException('Both username and password MUST NOT exceed a length of 255 bytes each');
        }
        if ($this->protocolVersion !== null && $this->protocolVersion !== '5') {
            throw new UnexpectedValueException('Authentication requires SOCKS5. Consider using protocol version 5 or waive authentication');
        }
        $this->auth = pack('C2', 0x01, strlen($username)) . $username . pack('C', strlen($password)) . $password;
    }

    public function unsetAuth()
    {
        $this->auth = null;
    }

    /**
     * Creates a Connector instance that can be used to establish TCP connections to remote hosts.
     *
     * This return a new `Connector` instance which can then be used to establish
     * any number of TCP connections.
     *
     * @return Connector
     * @see Connector
     */
    public function createConnector()
    {
        return new Connector($this);
    }

    /**
     * Creates a SecureConnector instance that can be used to establish encrypted TLS connections to remote hosts.
     *
     * This is actually a convenience helper method that uses React's normal
     * `SecureConnector` wrapper and this libraries' `Connector` for the
     * underlying TCP connection.
     *
     * @param array $sslContext optional SSL context options
     * @return SecureConnector
     * @uses self::createConnector()
     */
    public function createSecureConnector(array $sslContext = array())
    {
        return new SecureConnector($this->createConnector(), $this->loop, $sslContext);
    }

    /**
     * Should not be called directly, use createConnector() instead.
     *
     * This method contains the internal implementation for estasblishing a
     * connection to the SOCKS server.
     *
     * @param string $host
     * @param int    $port
     * @return Promise Promise<Stream,Exception>
     * @internal use self::createConnector() instead
     * @see self::createConnector()
     */
    public function createConnection($host, $port)
    {
        if (strlen($host) > 255 || $port > 65535 || $port < 0) {
            $deferred = new Deferred();
            $deferred->reject(new InvalidArgumentException('Invalid target specified'));
            return $deferred->promise();
        }
        $deferred = new Deferred();

        $timestampTimeout = microtime(true) + $this->timeout;
        $timerTimeout = $this->loop->addTimer($this->timeout, function () use ($deferred) {
            $deferred->reject(new Exception('Timeout while connecting to socks server'));
            // TODO: stop initiating connection and DNS query
        });

        // create local references as these settings may change later due to its async nature
        $auth = $this->auth;
        $protocolVersion = $this->protocolVersion;

        // protocol version not explicitly set?
        if ($protocolVersion === null) {
            // authentication requires SOCKS5, otherwise use SOCKS4a
            $protocolVersion = ($auth === null) ? '4a' : '5';
        }

        $loop = $this->loop;
        $that = $this;

        // simultaneously start TCP connection and DNS resolution
        $connecting = $this->connector->create($this->socksHost, $this->socksPort);
        $resolving = $this->resolve($host);

        $deferred->resolve($connecting->then(
            function (Stream $stream) use ($that, $resolving, $loop, $timerTimeout, $protocolVersion, $auth, $timestampTimeout, $port) {
                // connection established, wait for DNS resolver
                return $resolving->then(
                    function ($host) use ($stream, $port, $timestampTimeout, $that, $loop, $timerTimeout, $protocolVersion, $auth) {
                        // DNS resolver completed => cancel timeout
                        $loop->cancelTimer($timerTimeout);

                        $timeout = max($timestampTimeout - microtime(true), 0.1);
                        return $that->handleConnectedSocks($stream, $host, $port, $timeout, $protocolVersion, $auth);
                    },
                    function ($error) {
                        throw new Exception('Unable to resolve remote hostname', 0, $error);
                    }
                );
            },
            function ($error) use ($loop, $timerTimeout) {
                $loop->cancelTimer($timerTimeout);
                throw new Exception('Unable to connect to socks server', 0, $error);
            }
        ));

        return $deferred->promise();
    }

    private function resolve($host)
    {
        // return if it's already an IP or we want to resolve remotely (socks 4 only supports resolving locally)
        if (false !== filter_var($host, FILTER_VALIDATE_IP) || ($this->protocolVersion !== '4' && !$this->resolveLocal)) {
            $deferred = new Deferred();
            $deferred->resolve($host);
            return $deferred->promise();
        }

        return $this->resolver->resolve($host);
    }

    /**
     * Internal helper used to handle the communication with the SOCKS server
     *
     * @param Stream      $stream
     * @param string      $host
     * @param int         $port
     * @param float       $timeout
     * @param string      $protocolVersion
     * @param string|null $auth
     * @return Promise Promise<stream, Exception>
     */
    public function handleConnectedSocks(Stream $stream, $host, $port, $timeout, $protocolVersion, $auth=null)
    {
        $deferred = new Deferred();

        $timerTimeout = $this->loop->addTimer($timeout, function () use ($deferred) {
            $deferred->reject(new Exception('Timeout while establishing socks session'));
        });

        $reader = new StreamReader($stream);
        $stream->on('data', array($reader, 'write'));

        if ($protocolVersion === '5' || $auth !== null) {
            $promise = $this->handleSocks5($stream, $host, $port, $auth, $reader);
        } else {
            $promise = $this->handleSocks4($stream, $host, $port, $reader);
        }
        $promise->then(function () use ($deferred, $stream) {
            $deferred->resolve($stream);
        }, function($error) use ($deferred) {
            $deferred->reject(new Exception('Unable to communicate...', 0, $error));
        });

        $loop = $this->loop;
        $deferred->promise()->then(
            function (Stream $stream) use ($timerTimeout, $loop, $reader) {
                $loop->cancelTimer($timerTimeout);
                $stream->removeAllListeners('end');

                $stream->removeListener('data', array($reader, 'write'));

                return $stream;
            },
            function ($error) use ($stream, $timerTimeout, $loop, $reader) {
                $loop->cancelTimer($timerTimeout);
                $stream->close();

                $stream->removeListener('data', array($reader, 'write'));

                return $error;
            }
        );

        $stream->on('end', function (Stream $stream) use ($deferred) {
            $deferred->reject(new Exception('Premature end while establishing socks session'));
        });

        return $deferred->promise();
    }

    protected function handleSocks4(Stream $stream, $host, $port, StreamReader $reader)
    {
        // do not resolve hostname. only try to convert to IP
        $ip = ip2long($host);

        // send IP or (0.0.0.1) if invalid
        $data = pack('C2nNC', 0x04, 0x01, $port, $ip === false ? 1 : $ip, 0x00);

        if ($ip === false) {
            // host is not a valid IP => send along hostname (SOCKS4a)
            $data .= $host . pack('C', 0x00);
        }

        $stream->write($data);

        return $reader->readBinary(array(
            'null'   => 'C',
            'status' => 'C',
            'port'   => 'n',
            'ip'     => 'N'
        ))->then(function ($data) {
            if ($data['null'] !== 0x00 || $data['status'] !== 0x5a) {
                throw new Exception('Invalid SOCKS response');
            }
        });
    }

    protected function handleSocks5(Stream $stream, $host, $port, $auth=null, StreamReader $reader)
    {
        // protocol version 5
        $data = pack('C', 0x05);
        if ($auth === null) {
            // one method, no authentication
            $data .= pack('C2', 0x01, 0x00);
        } else {
            // two methods, username/password and no authentication
            $data .= pack('C3', 0x02, 0x02, 0x00);
        }
        $stream->write($data);

        $that = $this;

        return $reader->readBinary(array(
            'version' => 'C',
            'method'  => 'C'
        ))->then(function ($data) use ($auth, $stream, $reader) {
            if ($data['version'] !== 0x05) {
                throw new Exception('Version/Protocol mismatch');
            }

            if ($data['method'] === 0x02 && $auth !== null) {
                // username/password authentication requested and provided
                $stream->write($auth);

                return $reader->readBinary(array(
                    'version' => 'C',
                    'status'  => 'C'
                ))->then(function ($data) {
                    if ($data['version'] !== 0x01 || $data['status'] !== 0x00) {
                        throw new Exception('Username/Password authentication failed');
                    }
                });
            } else if ($data['method'] !== 0x00) {
                // any other method than "no authentication"
                throw new Exception('Unacceptable authentication method requested');
            }
        })->then(function () use ($stream, $reader, $host, $port) {
            // do not resolve hostname. only try to convert to (binary/packed) IP
            $ip = @inet_pton($host);

            $data = pack('C3', 0x05, 0x01, 0x00);
            if ($ip === false) {
                // not an IP, send as hostname
                $data .= pack('C2', 0x03, strlen($host)) . $host;
            } else {
                // send as IPv4 / IPv6
                $data .= pack('C', (strpos($host, ':') === false) ? 0x01 : 0x04) . $ip;
            }
            $data .= pack('n', $port);

            $stream->write($data);

            return $reader->readBinary(array(
                'version' => 'C',
                'status'  => 'C',
                'null'    => 'C',
                'type'    => 'C'
            ));
        })->then(function ($data) use ($reader) {
            if ($data['version'] !== 0x05 || $data['status'] !== 0x00 || $data['null'] !== 0x00) {
                throw new Exception('Invalid SOCKS response');
            }
            if ($data['type'] === 0x01) {
                // IPv4 address => skip IP and port
                return $reader->readLength(6);
            } else if ($data['type'] === 0x03) {
                // domain name => read domain name length
                return $reader->readBinary(array(
                    'length' => 'C'
                ))->then(function ($data) use ($that) {
                    // skip domain name and port
                    return $that->readLength($data['length'] + 2);
                });
            } else if ($data['type'] === 0x04) {
                // IPv6 address => skip IP and port
                return $reader->readLength(18);
            } else {
                throw new Exception('Invalid SOCKS reponse: Invalid address type');
            }
        });
    }
}
