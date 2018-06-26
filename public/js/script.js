function() {

  var sections = $('section');

  //+55px to offset the margin-top
  var $this = $(this),
    top = ($this.offset().top - $(window).scrollTop()) + 55,
    right = $this.offset().right;


  $this.css({
      position: 'fixed',
      top: top,
      right: right
    })
    .animate({
      right: '5%',
      top: '90%'
    }, 600)
    .addClass('clicked');

  var target;

  // END OF ARRAY HAS ALEADY BEEN REACHED
  if (count > (sections.length - 1)) {
    count = -1;

    smoothScroll('toTop');
    $this.removeClass('rotate');
  }
  // JUST REACHED END OF ARRAY
  else {
    target = $(sections[count]);
    if (count === (sections.length - 1)) {
      $this.addClass('rotate');
    }
    smoothScroll(target);
  }

  count++;

  $(this).find('.arrow-bounce').removeClass('arrow-bounce');

}