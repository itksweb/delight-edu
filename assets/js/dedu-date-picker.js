console.log("dedu-date-picker.js loaded")

class CustomDatePicker {
  constructor(containerElement, config = {}) {
    if (!containerElement) {
      console.error("CustomDatePicker missing a valid container element.");
      return;
    }
    this.container = containerElement;

    this.currentDate = new Date();
    this.displayedYear = this.currentDate.getFullYear();
    this.displayedMonth = this.currentDate.getMonth();
    this.selectedDate = null;

    // Feature Configurations
    this.disableWeekends = config.disableWeekends || false;
    this.hideWeekends = config.hideWeekends || false;
    this.dateFormat = config.dateFormat || "MM/DD/YYYY";

    const parseDateString = (dateStr) => {
      if (!dateStr) return null;
      const [year, month, day] = dateStr.split("-").map(Number);
      return new Date(year, month - 1, day);
    };

    this.minDate = parseDateString(config.minDate);
    this.maxDate = parseDateString(config.maxDate);

    if (this.minDate) this.minDate.setHours(0, 0, 0, 0);
    if (this.maxDate) this.maxDate.setHours(23, 59, 59, 999);

    this.disabledDatesTimestamps = new Set(
      (config.disabledDates || [])
        .map((dateStr) => {
          const parsed = parseDateString(dateStr);
          return parsed ? parsed.setHours(0, 0, 0, 0) : null;
        })
        .filter(Boolean),
    );

    this.months = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ];

    // SCOPED DOM CACHING: Searching ONLY within this specific component container
    this.input = this.container.querySelector(".date-input");
    this.dropdown = this.container.querySelector(".calendar-dropdown");
    this.grid = this.container.querySelector(".calendar-days");
    this.weekdaysHeader = this.container.querySelector(".calendar-weekdays");
    this.label = this.container.querySelector(".month-year-label");
    this.prevBtn = this.container.querySelector(".prev-btn");
    this.nextBtn = this.container.querySelector(".next-btn");

    this.init();
  }

  init() {
    this.dropdown.style.display = "none";
    this.input.addEventListener("click", () => this.toggleDropdown());
    this.prevBtn.addEventListener("click", () => this.prevMonth());
    this.nextBtn.addEventListener("click", () => this.nextMonth());
    document.addEventListener("click", (e) => {
      if (!this.container.contains(e.target)) {
        this.dropdown.style.display = "none";
      }
    });
    this.render();
  }

  toggleDropdown() {
    const isHidden = this.dropdown.style.display === "none";
    this.dropdown.style.display = isHidden ? "block" : "none";
  }

  getDaysInMonth(year, month) {
    return new Date(year, month + 1, 0).getDate();
  }

  getFirstDayOfWeek(year, month) {
    return new Date(year, month, 1).getDay();
  }

  // Consolidated boundary verification function
  isDateDisabled(year, month, day) {
    const targetDate = new Date(year, month, day);
    const dayOfWeek = targetDate.getDay(); // 0 = Sunday, 6 = Saturday

    // 1. Check Weekend rules
    if (
      (this.disableWeekends || this.hideWeekends) &&
      (dayOfWeek === 0 || dayOfWeek === 6)
    ) {
      return true;
    }

    // 2. Check Min/Max Range bounds
    if (this.minDate && targetDate < this.minDate) return true;
    if (this.maxDate && targetDate > this.maxDate) return true;

    // 3. Check specific array values (holidays / booked slots)
    if (this.disabledDatesTimestamps.has(targetDate.setHours(0, 0, 0, 0))) {
      return true;
    }

    return false;
  }

  nextMonth() {
    if (this.maxDate) {
      const nextMonthFirstDay = new Date(
        this.displayedYear,
        this.displayedMonth + 1,
        1,
      );
      if (nextMonthFirstDay > this.maxDate) return;
    }
    if (this.displayedMonth === 11) {
      this.displayedMonth = 0;
      this.displayedYear++;
    } else {
      this.displayedMonth++;
    }
    this.render();
  }

  prevMonth() {
    if (this.minDate) {
      const prevMonthLastDay = new Date(
        this.displayedYear,
        this.displayedMonth,
        0,
      );
      if (prevMonthLastDay < this.minDate) return;
    }
    if (this.displayedMonth === 0) {
      this.displayedMonth = 11;
      this.displayedYear--;
    } else {
      this.displayedMonth--;
    }
    this.render();
  }

  // Helper template generator to create day cells cleanly
  createDayCell(day, year, month, typeClass) {
    const dayCell = document.createElement("div");
    dayCell.textContent = day;

    if (this.isDateDisabled(year, month, day)) {
      // If we want weekends completely gone from existence, don't even mount the cell
      if (this.hideWeekends) {
        const targetDate = new Date(year, month, day);
        if (targetDate.getDay() === 0 || targetDate.getDay() === 6) return null;
      }
      dayCell.classList.add("calendar-day", "disabled");
    } else {
      dayCell.classList.add("calendar-day", typeClass);

      if (
        typeClass === "current" &&
        this.selectedDate &&
        this.selectedDate.getDate() === day &&
        this.selectedDate.getMonth() === month &&
        this.selectedDate.getFullYear() === year
      ) {
        dayCell.classList.add("selected");
      }

      dayCell.addEventListener("click", () => {
        if (typeClass === "prev") this.prevMonth();
        if (typeClass === "next") this.nextMonth();
        this.selectDate(day);
      });
    }
    return dayCell;
  }

  render() {
    this.grid.innerHTML = "";
    this.label.textContent = `${this.months[this.displayedMonth]} ${this.displayedYear}`;

    // Adjust UI layouts if we are hiding weekend elements completely
    if (this.hideWeekends) {
      this.weekdaysHeader.classList.add("hide-weekends");
      this.grid.classList.add("hide-weekends");
      this.weekdaysHeader.innerHTML =
        "<div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div>";
    } else {
      this.weekdaysHeader.classList.remove("hide-weekends");
      this.grid.classList.remove("hide-weekends");
      this.weekdaysHeader.innerHTML =
        "<div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>";
    }

    const totalDays = this.getDaysInMonth(
      this.displayedYear,
      this.displayedMonth,
    );
    const startOffset = this.getFirstDayOfWeek(
      this.displayedYear,
      this.displayedMonth,
    );

    const prevMonth = this.displayedMonth === 0 ? 11 : this.displayedMonth - 1;
    const prevYear =
      this.displayedMonth === 0 ? this.displayedYear - 1 : this.displayedYear;
    const totalDaysInPrevMonth = this.getDaysInMonth(prevYear, prevMonth);

    // 1. Render Trailing Days
    for (let i = startOffset - 1; i >= 0; i--) {
      const prevDay = totalDaysInPrevMonth - i;
      const cell = this.createDayCell(
        prevDay,
        prevYear,
        prevMonth,
        "adjacent-month",
      );
      if (cell) this.grid.appendChild(cell);
    }

    // 2. Render Current Month Days
    for (let day = 1; day <= totalDays; day++) {
      const cell = this.createDayCell(
        day,
        this.displayedYear,
        this.displayedMonth,
        "current",
      );
      if (cell) this.grid.appendChild(cell);
    }

    // 3. Render Leading Days to balance the rectangular grid
    const nextMonth = this.displayedMonth === 11 ? 0 : this.displayedMonth + 1;
    const nextYear =
      this.displayedMonth === 11 ? this.displayedYear + 1 : this.displayedYear;

    // Grid alignment calculations based on hidden layout configurations
    const currentCellsCount = this.grid.children.length;
    const maxTargetCells = this.hideWeekends ? 30 : 42; // 5 cols * 6 rows vs 7 cols * 6 rows
    const remainingCells = maxTargetCells - currentCellsCount;

    for (let nextDay = 1; nextDay <= remainingCells; nextDay++) {
      const cell = this.createDayCell(
        nextDay,
        nextYear,
        nextMonth,
        "adjacent-month",
      );
      if (cell) this.grid.appendChild(cell);
    }

    // 4. Update Header Nav Disablement states
    if (this.minDate) {
      const prevMonthLastDay = new Date(
        this.displayedYear,
        this.displayedMonth,
        0,
      );
      this.prevBtn.disabled = prevMonthLastDay < this.minDate;
    }
    if (this.maxDate) {
      const nextMonthFirstDay = new Date(
        this.displayedYear,
        this.displayedMonth + 1,
        1,
      );
      this.nextBtn.disabled = nextMonthFirstDay > this.maxDate;
    }
  }

  selectDate(day) {
    this.selectedDate = new Date(this.displayedYear, this.displayedMonth, day);
    const formattedDate = `${this.displayedMonth + 1}/${day}/${this.displayedYear}`;
    this.input.value = formattedDate;
    this.dropdown.style.display = "none";
    this.render();
  }
}


