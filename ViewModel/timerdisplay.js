function updateClock() {
    const clock = document.getElementById("clock");
    const now = new Date();
  
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const date = now.toLocaleDateString(undefined, options);
    const time = now.toLocaleTimeString();
  
    clock.textContent = `${date} - ${time}`;
  }
  
  // Update the clock every second
  setInterval(updateClock, 1000);
  
  // Initialize the clock immediately
  updateClock();
  