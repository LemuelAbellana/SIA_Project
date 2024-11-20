document.addEventListener("DOMContentLoaded", () => {
  const newMemberBtn = document.getElementById("newMemberBtn");
  const popupForm = document.getElementById("popupForm");
  const closePopup = document.getElementById("closePopup");
  const cancelBtn = document.getElementById("cancelBtn");

  // Show popup
  newMemberBtn.addEventListener("click", () => {
    popupForm.style.display = "flex";
  });

  // Close popup (via the close button)
  closePopup.addEventListener("click", () => {
    popupForm.style.display = "none";
  });

  // Cancel form and close popup (via the cancel button)
  cancelBtn.addEventListener("click", () => {
    popupForm.style.display = "none";
  });
});
