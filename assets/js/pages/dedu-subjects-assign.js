console.log("dedu-subjects-assign.js loadedd");

const subjectRows = document.getElementById("subject-rows");
const belowMulti = document.getElementById("below-select");
const addRowBtn = document.querySelector("#add-row");

const creatIt = (tag = "span", cls = "", text = "") => {
  const element = document.createElement(tag);
  if (cls) element.classList.add(cls);
  if (text) element.textContent = text;
  return element;
};


const customiseMultiselect = (tr = null) => {
  const base = tr ? tr : document;

  const updateMultiSelectDisplay = (content) => {
    const select = content.closest(".multi-select").querySelector("select");
    const label = content
      .closest(".multi-select")
      .querySelector(".selected-tags");
    const options = content.querySelectorAll(".multi-select-option");

    const values = [...select.selectedOptions].map((opt) => opt.value);
    label.innerHTML = "";
    if (values.length) {
      values.forEach((value) => {
        const optMatch = [...options].find(
          (opt) => opt.dataset.value === value,
        );
        if (optMatch) {
          const tag = creatIt("span", "selected-tag", optMatch.textContent);
          label.appendChild(tag);
        }
      });
    } else {
      const placeholder = creatIt("span", "placeholder", "choose");
      label.appendChild(placeholder);
    }
  };

  const handleSelection = (content) => {
    const select = content.closest(".multi-select").querySelector("select");
    content.addEventListener("click", (e) => {
      const option = e.target.closest('[role="option"]');
      if (!option) return;
      const realOption = [...select.options].find(
        (o) => o.value === option.dataset.value,
      );
      realOption.selected = !realOption.selected;
      option.setAttribute("aria-selected", realOption.selected);
      option.classList.contains("selected")
        ? option.classList.remove("selected")
        : option.classList.add("selected");

      //Update selected items display
      updateMultiSelectDisplay(content);
    });
  };

  const allMultiSelects = base.querySelectorAll(".multi-select");
  const checkOption = `<svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="feather feather-check"
                >
                    <polyline points="20 6 9 17 4 12"></polyline>
  </svg>`;

  const toggleBtn = () => {
    const tooggleBtn = creatIt("button", "multi-select-button");
    tooggleBtn.setAttribute("type", "button");
    const btnInner = `<span class="selected-tags">
            <span class="placeholder">Choose</span>
        </span>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
            />
      </svg>`;
    tooggleBtn.innerHTML = btnInner;
    tooggleBtn.setAttribute("aria-haspopup", "listbox");
    tooggleBtn.setAttribute("aria-expanded", "false");
    return tooggleBtn;
  };

  //Build custom dropdown options
  allMultiSelects.forEach((item) => {
    const select = item.querySelector("select");
    const btn = toggleBtn();
    const ul = creatIt("ul", "dropdown-content");
    ul.setAttribute("role", "listbox");
    ul.setAttribute("hidden", "true");
    ul.setAttribute("aria-multiselectable", "true");
    const options = [...select.options]
      .map((opt) => {
        const selected = opt.selected ? "selected" : "";
        return `<li role = "option" aria-selected = ${opt.selected} data-value = ${opt.value} class = "multi-select-option ${selected}"><span>${opt.text}</span><span class = "checkbox-icon" >${checkOption}</span></li>`;
      })
      .join("");
    ul.innerHTML = options;
    select.hidden = true;
    item.appendChild(btn);
    item.appendChild(ul);
  });
  console.log("done building options")

  const selectToggles = base.querySelectorAll(".multi-select-button");

  //handling dropdown toggle
  selectToggles.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", !expanded);
      const content = btn.parentElement.querySelector("ul");
      console.log(content);
      content.hidden = expanded;
    });
  });

  const allDropdownContent = base.querySelectorAll(".dropdown-content");

  //handle selection
  allDropdownContent.forEach((content) => handleSelection(content));

  //close dropdown on click outside
  window.addEventListener("click", function (e) {
    selectToggles.forEach((btn) => {
      const content = btn.parentElement.querySelector(".dropdown-content");

      if (!btn.contains(e.target) && content.hidden == false) {
        content.hidden = true;
        btn.setAttribute("aria-expanded", false);
      }
    });
  });
  return tr;
};

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
      const selected =
        data && data.teachers.includes(t.id.toString()) ? "selected" : "";
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
  console.log("het")
  const theRow = customiseMultiselect(tr);
  subjectRows.appendChild(theRow);
}

const renderEditScreen = (data) => {
  const { name, id } = data;
  addRowBtn.dataset.id = id;
  document.querySelector(`input[name="class_id"]`).value = id;
  let curriculum = data.curriculum ? JSON.parse(data.curriculum) : [];
  subjectRows.replaceChildren();
  console.log("id: ", id);
  console.log("name: ", name);
  console.log("curriculum: ", curriculum);
  if (curriculum.length) {
    curriculum.forEach((item) => {
      addNewRow(id, item);
    });
  } else {
    // addNewRow(id);
  }
};

addRowBtn.addEventListener("click", (e) => {
  addNewRow(e.target.dataset.id);
});
subjectRows.addEventListener("click", (e) => {
  if (e.target.classList.contains("remove-row")) {
    e.target.closest("tr").remove();
  }
});
