document.addEventListener("DOMContentLoaded", () => {
  const newMemberBtn = document.getElementById("newMemberBtn");
  const popupForm = document.getElementById("popupForm");
  const closePopup = document.getElementById("closePopup");

  // Show popup
  newMemberBtn.addEventListener("click", () => {
    popupForm.style.display = "flex";
  });

  // Close popup
  closePopup.addEventListener("click", () => {
    popupForm.style.display = "none";
  });

  // Close popup when clicking outside the content
  popupForm.addEventListener("click", (e) => {
    if (e.target === popupForm) {
      popupForm.style.display = "none";
    }
  });
});
