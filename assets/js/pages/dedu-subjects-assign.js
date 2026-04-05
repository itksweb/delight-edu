console.log("dedu-subjects-assign.js loadedd");

const subjectRows = document.querySelector("#subject-rows");
const addRowBtn = document.querySelector("#add-row");

function addNewRow(id, data = null) {
  // Replaces: $("#subject-rows tr").length
  const index = subjectRows.getElementsByTagName("tr").length;
  const availableSections = DEDU_MASTER_DATA.sections[id] || [];

  // Build Subject Options (using .map and .join for cleaner string building)
  let subjectOptions = '<option value="">-- Select Subject --</option>';
  subjectOptions += DEDU_MASTER_DATA.subjects
    .map((sub) => {
      const selected = data && data.subject_id == sub.id ? "selected" : "";
      return `<option value="${sub.id}" ${selected}>${sub.subject_name}</option>`;
    })
    .join("");

  // Build Section Options
  const sectionOptions = availableSections
    .map((sec) => {
      const selected =
        data && data.sections.includes(+sec.id) ? "selected" : "";
      return `<option value="${sec.id}" ${selected}>${sec.section_name}</option>`;
    })
    .join("");

  // Build Teacher Options
  const teacherOptions = DEDU_MASTER_DATA.teachers
    .map((t) => {
      const selected = data && data.teachers.includes(+t.id) ? "selected" : "";
      return `<option value="${t.id}" ${selected}>${t.first_name} ${t.last_name}</option>`;
    })
    .join("");

  const rowHtml = `
    <td style="width: 25%;"><select name="subjects[${index}][id]" required style="width:100%;">${subjectOptions}</select></td>
    <td style="width: 15%;"><input type="text" placeholder="e.g. MAT101" name="subjects[${index}][code]" value="${
    data ? data.subject_code : ""
  }" style="width:100%;"></td>
    <td class="multi-select" style="width: 25%;"><select name="subjects[${index}][sections][]" multiple class="" style="width:100%;">${sectionOptions}</select></td>
    <td class="multi-select" style="width: 25%;"><select name="subjects[${index}][teachers][]" multiple class="" style="width:100%;">${teacherOptions}</select></td>
    <td style="width: 10%; text-align: center;"><button type="button" class="remove-row"><span class="dashicons dashicons-trash" style="color:#a00;"></span></button></td>`;

  // Create the tr element
  const tr = creatIt("tr");
  tr.innerHTML = rowHtml;

  //customise the multi select in the row and append to the table body
  const theRow = customiseMultiselect(tr);
  subjectRows.appendChild(theRow);
}

const renderEditScreen = (e) => {
  const data = target(e, ".dedu-edit-icon").dataset;
  const { id } = data;
  addRowBtn.dataset.id = id;
  document.querySelector(`input[name="class_id"]`).value = id;
  let curriculum = data.curriculum ? JSON.parse(data.curriculum) : [];
  subjectRows.replaceChildren();
  console.log("curriculum: ", curriculum);
  if (curriculum.length) {
    curriculum.forEach((item) => {
      addNewRow(id, item);
    });
  }
  updateUrlActionId(id);
  showFormView();
};

document.addEventListener("click", (e) => {
  if (target(e, ".remove-row")) target(e, "tr").remove();
  else if (target(e, "#add-row")) addNewRow(e.target.dataset.id);
});

document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.querySelector("#subject-rows");
  const submitBtn = document.querySelector(".button-primary");

  const validateCurriculum = () => {
    let selectedSubjects = [];
    let hasDuplicate = false;

    // 1. Find all select menus whose name contains "[id]"
    const selects = tbody.querySelectorAll('select[name*="[id]"]');

    selects.forEach((select) => {
      const val = select.value;

      if (val) {
        if (selectedSubjects.includes(val)) {
          // 2. Set red border for duplicates
          select.style.border = "2px solid #d63638";
          hasDuplicate = true;
        } else {
          // 3. Clear border and track the value
          select.style.border = "";
          selectedSubjects.push(val);
        }
      }
    });

    // 4. Update submit button state
    if (submitBtn) {
      submitBtn.disabled = hasDuplicate;
      if (hasDuplicate) {
        submitBtn.setAttribute(
          "title",
          "Each subject can only be assigned once per class."
        );
      } else {
        submitBtn.removeAttribute("title");
      }
    }
  }

  // 5. Event Delegation: Listen for changes on the tbody
  tbody?.addEventListener("change", (e) => {
    if (e.target.matches('select[name*="[id]"]')) {
      validateCurriculum();
    }
  });
});
