const currentDate = new Date();
const year = currentDate.getFullYear();
let month = currentDate.getMonth() + 1;
let day = currentDate.getDate();
const hours = currentDate.getHours();
const minutes = currentDate.getMinutes();

month = month < 10 ? "0" + month : month;
day = day < 10 ? "0" + day : day;
const currentTime = `${hours < 10 ? "0" + hours : hours}:${minutes < 10 ? "0" + minutes : minutes}`;

// Set the minimum date and time to the current date and current time
const minDateTime = `${year}-${month}-${day}T${currentTime}`;
document.getElementById("arrivalDate").min = minDateTime;
document.getElementById("leavingDate").min = minDateTime;

// Ensure that 'leavingDate' cannot be before 'arrivalDate'
document.getElementById("arrivalDate").addEventListener("change", (event) => {
  const arrivalDateTime = event.target.value;
  document.getElementById("leavingDate").min = arrivalDateTime;
});
