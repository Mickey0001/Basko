<footer class="text-center col-md-12">
    <a ref="https://www.facebook.com/BASHESKIA-EDWARD-EQ-83892532107/" target="_blank"><i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i></a>
    <a href="https://www.youtube.com/user/staripionir" target="_blank"><i class="fa fa-youtube-square fa-2x" aria-hidden="true"></i></a>
    <a href="https://open.spotify.com/artist/75bkgEjebXg7u6CWDyNz77" target="_blank"><i class="fa fa-spotify fa-2x" aria-hidden="true"></i></a>
    <a href="https://soundcloud.com/basheskia" target="_blank"><i class="fa fa-soundcloud fa-2x" aria-hidden="true"></i></a>
  </a><br><br>
  <p>Basheskia &amp; Edward EQ &copy; <?php echo date("Y"); ?></a></p>
</footer>

<script defer>
$(document).ready(function(){
  // Initialize Tooltip
  $('[data-toggle="tooltip"]').tooltip();

  // Add smooth scrolling to all links in navbar + footer link
  $(".navbar a, footer a[href='#myPage']").on('click', function(event) {

    // Prevent default anchor click behavior
    event.preventDefault();

    // Store hash
    var hash = this.hash;

    // Using jQuery's animate() method to add smooth page scroll
    // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
    $('html, body').animate({
      scrollTop: $(hash).offset().top
    }, 900, function(){

      // Add hash (#) to URL when done scrolling (default click behavior)
      window.location.hash = hash;
    });
  });
})
</script>
</body></html>