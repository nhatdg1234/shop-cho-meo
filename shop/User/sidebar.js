document.querySelectorAll('.menu-parent__title').forEach(function (btn) {
  btn.addEventListener('click', function () {
    const parentLi = btn.closest('.menu-parent');
    const isOpen = parentLi.classList.contains('open');

    document.querySelectorAll('.menu-parent.open').forEach(function (li) {
      if (li !== parentLi) {
        li.classList.remove('open');
        li.querySelector('.menu-parent__title').setAttribute('aria-expanded', 'false');
      }
    });

    parentLi.classList.toggle('open', !isOpen);
    btn.setAttribute('aria-expanded', String(!isOpen));
  });
});
