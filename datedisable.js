const currentDate = new Date();
const year = currentDate.getFullYear();
let month = currentDate.getMonth() + 1; 
let day = currentDate.getDate(); 

month = month < 10 ? "0" + month : month;
day = day < 10 ? "0" + day : day;

const minDate = `${year}-${month}-${day}T00:00`;
document.getElementById("arrivalDate").min = minDate;
document.getElementById("leavingDate").min = minDate;
