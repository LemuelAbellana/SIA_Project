const currentDate = new Date();
const year = currentDate.getFullYear();
let month = currentDate.getMonth() + 1;
let day = currentDate.getDate();
const hours = currentDate.getHours();
const minutes = currentDate.getMinutes();

month = month < 10 ? "0" + month : month;
day = day < 10 ? "0" + day : day;
const currentTime = `${hours < 10 ? "0" + hours : hours}:${minutes < 10 ? "0" + minutes : minutes}`;

const minDateTime = `${year}-${month}-${day}T${currentTime}`;
document.getElementById("arrival_date").min = minDateTime;
document.getElementById("leaving_date").min = minDateTime;

document.getElementById("arrival_date").addEventListener("change", (event) => {
  const arrivalDateTime = event.target.value;
  document.getElementById("leaving_date").min = arrivalDateTime;
});
