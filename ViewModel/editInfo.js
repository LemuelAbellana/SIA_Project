document.addEventListener("DOMContentLoaded", () => {
    const popupForm = document.getElementById("edit-Form");
    const closePopup = document.getElementById("closeEdit");
    const cancelBtn = document.getElementById("cancelEdit");
  
    // Select all buttons with the class 'editBtn'
    const editButtons = document.querySelectorAll(".editBtn");
  
    // Add event listeners to each edit button
    editButtons.forEach((button) => {
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
  