document.addEventListener("DOMContentLoaded", () => {
    const popupForm = document.getElementById("view-Form");
    const closePopup = document.getElementById("closeView");
    const cancelBtn = document.getElementById("cancelBtn");
  
    // Select all buttons with the class 'viewBtn'
    const viewButtons = document.querySelectorAll(".viewBtn");
  
    // Add event listeners to each button
    viewButtons.forEach((button) => {
      button.addEventListener("click", () => {
        popupForm.style.display = "flex";
      });
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
  