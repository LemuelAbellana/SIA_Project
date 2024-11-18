document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
      const faqItem = button.parentElement;
      faqItem.classList.toggle('active');
      button.querySelector('.icon').innerHTML = faqItem.classList.contains('active') ? '<i class="fa-solid fa-caret-up"></i>' : '<i class="fa-solid fa-caret-down"></i>';
    });
  });