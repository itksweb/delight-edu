console.log("dedu-class-attendance.js loaded");

const { classes, sections } = deduAttendance;
const classField = document.querySelector("#class-field");
const sectionsField = document.querySelector("#sections-field");
const attendanceDateField = document.querySelector("#attendance_date_input");
const studentRowTemplate = document.querySelector("#student-row");

const buildSectionOptionsAndPickOne = (classId = null, sectionId = null) => {
  let options = `<option value = "" disabled selected>-- select a class first --</option>`;
  if (classId) {
    const hasSections = sections[classId] && sections[classId].length;
    const class_name = classes.find((cls) => cls.id === classId).class_name;
    if (hasSections) {
      options = `<option value = "">All Sections</option>`;
      options += sections[classId]
        .map((sec) => {
          const selected = sec.id === sectionId ? "selected" : "";
          return `<option value="${sec.id}" ${selected}>${sec.section_name}</option>`;
        })
        .join("");
    } else {
      options = `<option value = "" selected disabled>-- no sections for ${class_name} --</option>`;
    }
  }
  sectionsField.innerHTML = options;
};

document.addEventListener("DOMContentLoaded", () => {
  const filterForm = document.querySelector("#dedu-attendance-filter-form");
  const rosterCard = document.querySelector("#dedu-attendance-roster-card");
  const studentsBody = document.querySelector("#attendance-students-body");
  const termIdInput = document.querySelector("input[name='term_id']");
  const sheetForm = document.querySelector("#dedu-attendance-sheet-form");
  const actionType = document.querySelector("#btn-action");

  // Load the active term date strings from our shared localized global configuration array object
  // to restrict the HTML5 date picker inputs instantly
  const dateInput = document.querySelector("#attendance_date_input");
  if (dateInput && window.deduAttendance) {
    const activeTerm = deduAttendance.active_term;
    const minDate = deduAttendance.active_term.starts
      ? deduAttendance.active_term.starts
      : null;
    const maxDate = deduAttendance.active_term.ends
      ? deduAttendance.active_term.ends
      : null;
    const holidayList = []; //Array.isArray(deduSessionData.holidays) ? deduSessionData.holidays : [];
    const inputContainer = dateInput.parentElement;

    // Initialize Flatpickr gracefully
    new CustomDatePicker(inputContainer, {
      minDate,
      maxDate,
      hideWeekends: true,
    });
  }

  filterForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(filterForm);
    formData.append("action", "dedu_load_attendance_roaster");
    console.log("action value: ", actionType.value);

    try {
      const response = await fetch(ajaxurl, { method: "POST", body: formData });
      const result = await response.json();

      if (result.success) {
        termIdInput.value = result.data.term.id;
        console.log(result.data);
        studentsBody.replaceChildren();

        result.data.roster.forEach((student) => {
          const row = studentRowTemplate.content.cloneNode(true);
          row.querySelector(".text-heading").textContent = student.name;
          const sectionIdField = row.querySelector(
            "input[data-name='section_id']",
          );
          sectionIdField.name = `attendance[${student.student_id}][section_id]`;
          sectionIdField.value = student.section_id;
          const corr = row.querySelector(`input[value="${student.status}"]`);
          corr.checked = true;
          const statusSection = row.querySelector(".dedu-status-toggle-group");
          statusSection
            .querySelectorAll("input[type='radio']")
            .forEach((el, i) => {
              el.name = `attendance[${student.student_id}][status]`;
              el.value = ["present", "absent", "late"][i];
              actionType.value === "take"
                ? el.parentElement.classList.remove("read-only-checkbox")
                : el.parentElement.classList.add("read-only-checkbox");
            });

          const remarks = row.querySelector(".remarks");
          remarks.name = `attendance[${student.student_id}][remarks]`;
          remarks.value = student.remarks;
          actionType.value === "take"
            ? remarks.classList.remove("read-only-checkbox")
            : remarks.classList.add("read-only-checkbox");
          studentsBody.appendChild(row);
        });

        const sBtn = sheetForm.querySelector("button[type='submit']");
        sBtn.disabled = actionType.value !== "take";
        actionType.value === "take"
          ? sBtn.classList.remove("hide-me")
          : sBtn.classList.add("hide-me");

        rosterCard.classList.remove("hide-me");
      } else {
        alert(result.data);
      }
    } catch (err) {
      console.error("Failed loading sheet roster tracking parameters:", err);
    }
  });

  sheetForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    // 1. Gather all active layout nodes inside a unified data cluster instance
    const formData = new FormData(sheetForm);

    // 2. Inject context coordinates directly from the filter inputs parameters block
    formData.append("class_id", document.querySelector("#class-field").value);
    formData.append(
      "attendance_date",
      document.querySelector("#attendance_date_input").value,
    );
    formData.append("action", "dedu_save_attendance_sheet");
    console.log(Object.fromEntries(formData));

    try {
      const response = await fetch(ajaxurl, { method: "POST", body: formData });
      const result = await response.json();

      if (result.success) {
        if (window.triggerDeduToast) {
          window.triggerDeduToast("Roster log compiled successfully!");
        } else {
          alert("Success: Attendance sheet processed safely.");
        }
        filterForm.reset();
        studentsBody.replaceChildren();
        rosterCard.classList.add("hide-me");

        // Keep the roster open but update visual cues if needed,
        // or scroll smoothly back up to the selection parameters grid area panel!
        window.scrollTo({ top: 0, behavior: "smooth" });
      } else {
        alert("Error processing entries: " + result.data);
      }
    } catch (error) {
      console.error("Critical submission failure encountered:", error);
    }
  });

  document.addEventListener("click", (e) => {
    if (e.target.matches(".sub-btn")) actionType.value = e.target.id;
  });
});

// 1. Handle Toggles and Removal via Event Delegation
document.addEventListener("change", function (e) {
  if (e.target === classField) {
    buildSectionOptionsAndPickOne(classField.value);
  }
});
