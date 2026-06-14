document.addEventListener("DOMContentLoaded", () => {
  const dateInput = document.querySelector("#attendance_date_input");

  if (dateInput && window.deduSessionData) {
    const activeTerm = deduSessionData.active_term;
    const holidayList = Array.isArray(deduSessionData.holidays) ? deduSessionData.holidays : [];

    // Initialize Flatpickr gracefully
    flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      defaultDate: "today",
      // Force minimum and maximum boundaries based on current active term parameters
      minDate: activeTerm ? activeTerm.starts : null,
      maxDate: activeTerm ? activeTerm.ends : null,
      
      // 🔴 The Safety Rules Grid Array Matrix Layer
      disable: [
        // Rule 1: Custom inline worker function flags weekends as unavailable instantly
        function(date) {
          // Returns true (disabled) if day is Sunday (0) or Saturday (6)
          return (date.getDay() === 0 || date.getDay() === 6);
        },
        // Rule 2: Pass your array of string holidays straight down the line!
        ...holidayList
      ],
      
      // Clear out the roster layout instantly if the teacher switches to a new calendar box choice
      onChange: function(selectedDates, dateStr, instance) {
         const rosterCard = document.querySelector("#dedu-attendance-roster-card");
         if (rosterCard) {
             rosterCard.classList.add("hide-me");
         }
      }
    ]);
  }
});