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


});
