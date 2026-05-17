console.log("dedu-student.js loaded");
const parentsContainer = document.getElementById("parents-list");
const parentTemplate = document.querySelector("#entry");
const firstParent = parentsContainer.querySelector(".parent-entry");
const wpUser = document.querySelector("input[name='wp_user_id']");
const pixZone = document.querySelector(".dedu-upload-container:not(.sub-pix)");
const fname = document.querySelector('input[name="first_name"]');
const lname = document.querySelector('input[name="last_name"]');
const mname = document.querySelector('input[name="middle_name"]');
const email = document.querySelector('input[name="email"]');
const phone = document.querySelector('input[name="phone"]');
const maritals = document.querySelector('select[name="marital_status"]');
const bloodGroup = document.querySelector('select[name="blood_group"]');
const dob = document.querySelector('input[name="date_of_birth"]');
const address = document.querySelector('input[name="address"]');
const addBtn = document.getElementById("add-parent-btn");

const joiningDate = document.querySelector('input[name="joining_date"]');
const admissionNumber = document.querySelector('input[name="admission_no"]');
const classId = document.querySelector('select[name="class_id"]');
const sectionId = document.querySelector('select[name="section_id"]');
const password = document.querySelector('input[type="password"]');
const dropZones = document.querySelectorAll(".dedu-upload-container");
const classes = deduStudentData["classes"];
const sections = deduStudentData["sections"];
const allEntries = parentsContainer.querySelectorAll(
  ".existing-parent-selector select",
);
const allParents = deduStudentData["all_parents"];
let selectedParents = [];
let storedParents = [];



const disableOptions = (select, selected = [], storedParents = []) => {
  const toBeDisabled = [...selected, ...storedParents];
  const options = [...select.options];
  options.forEach((opt) => (opt.disabled = toBeDisabled.includes(opt.value)));
};

const updateParentOptions = () => {
  const allEntries = parentsContainer.querySelectorAll(
    ".existing-parent-selector select",
  );
  // Update selectedParents based on current selections
  selectedParents = [...allEntries]
    .map((select) => select.value)
    .filter((val) => val);
  console.log("Selected Parents: ", selectedParents);
  allEntries.forEach((select) => disableOptions(select, selectedParents, storedParents));
};

const populateParentOptions = (select, options) => {
  options.forEach((parent) => {
    const text = `${parent["first_name"]} ${parent["last_name"]}`;
    const opt = creatIt("option", "", text);
    opt.setAttribute("value", parent.id);
    select.appendChild(opt);
  });
};

const updateParentButtonState = () => {
  const allEntries = parentsContainer.querySelectorAll(".parent-entry");
  addBtn.disabled = allEntries.length >= 3;
};

// reset parent entries
const clearUnwantedParentEntries = () => {
  const allPas = parentsContainer.querySelectorAll(".parent-entry");
  allPas.forEach((pa) =>
    pa === firstParent ? pa.setAttribute("data-index", 0) : pa.remove(),
  );
};


// Update the array index in the name attribute: parents[0] -> parents[1]
const updateNameAttrForParent = (par, i) => {
  const inputs = par.querySelectorAll("input, select, textarea");
  const regex = /^parents\[\d+\]/;
  inputs.forEach((input) => {
    if (input.name && regex.test(input.name)) {
      input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
    }
  });
};

const parentModeSwitch = (par, mode = "", isDB = false) => {
  const newFields = par.querySelector(".parent-fields");
  const relationshipToggle = par.querySelector(".rel-switch");
  const requiredInputs = newFields.querySelectorAll(".parent-required");
  
  if (isDB && mode === "new") {
    newFields.classList.remove("hide-me");
    requiredInputs.forEach((input) => (input.required = true));
  } else if (!isDB) {
    const newExistingToggle = par.querySelector(".parent-mode-toggle");
    const guardianTop = par.querySelector(".dedu-parent-guardian-top");
    const existingSelector = par.querySelector(".existing-parent-selector");
    const existingParent = existingSelector.querySelector("select");
    if (mode === "new") {
      existingSelector.classList.add("hide-me");
      newFields.classList.remove("hide-me");
      relationshipToggle?.classList.remove("hide-me");
      existingParent.required = false;
      existingParent.value = "";
      requiredInputs.forEach((input) => (input.required = true));
      newExistingToggle?.classList.remove("wide");
      par.querySelector("legend").textContent = "Parent Details";
    } else if (mode === "existing") {
      newFields.classList.add("hide-me");
      existingSelector?.classList.remove("hide-me");
      relationshipToggle?.classList.add("hide-me");
      existingParent.required = true;
      requiredInputs.forEach((input) => (input.required = false));
      newExistingToggle?.classList.remove("wide");
      newFields
        .querySelectorAll("input, select, textarea")
        .forEach((field) => (field.value = ""));
    } else if (!mode) {
      existingSelector?.classList.add("hide-me");
      newFields.classList.add("hide-me");
      relationshipToggle?.classList.add("hide-me");
      newExistingToggle?.classList.add("wide");
    }
  }  
};

const populateFields = (form, data = {}) => {
  const inputs = form.querySelectorAll("input, select, textarea");
  // First reset all fields to default/empty values before populating with new data
  inputs.forEach((input) => {
    if (["radio", "checkbox"].includes(input.type)) {
      input.checked = false;
    } else if (input.type !== "hidden") {
      input.value = "";
    }
  });
  // Now populate with new data if available
  if (!isObjectEmpty(data)) {
    inputs.forEach((input) => {
      if (input.name) {
        const parField = input.name.startsWith("parents[");
        const key = parField
          ? input.name.split("][")[1].slice(0, -1)
          : input.name;
        if (!key.endsWith("_photo")) {
          input.value = data[key] ? data[key] : "";
        }
      }
    });
  }
};

const studentForm = document.querySelector("#student-form");
const classField = document.querySelector("#class-field");
const sectionsField = document.querySelector("#sections-field");

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;
  wpUser.value = "";
  storedParents = [];
  selectedParents = [];

  parentsContainer.replaceChildren(); // clear all parent entries
  populateFields(studentForm); // clear all fields
  joiningDate.value = todaysDate(); //set default date to today's date
  const newParent = parentTemplate.content.cloneNode(true);
  newParent.querySelector(".parent-entry").setAttribute("data-index", 0);
  const select = newParent.querySelector(".existing-parent-selector select");
  populateParentOptions(select, allParents);
  parentModeSwitch(newParent);
  parentsContainer.appendChild(newParent);
  updateParentButtonState();
  updateUrlActionId();
  updateHiddenInput();
  showFormView();
};

const renderEditScreen = async (e) => {
  const ID = target(e, ".dedu-edit-icon").dataset.id;

  // Prepare AJAX request
  const formData = new FormData();
  formData.append("action", "get_student_details");
  formData.append("id", ID);
  formData.append("nonce", deduStudentData.nonce);

  try {
    const response = await fetch(ajaxurl, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      const { student, parents } = result.data;
      console.log("data: ", result.data);
      storedParents = [...parents];
      selectedParents = [];

      // Populate Basic Fields
      formTitle.textContent = `Edit student: ${student.first_name} ${student.last_name}`;
      submitBtn.textContent = "Update student";
      updatePhoto(pixZone, student.photo_url);
      parentsContainer.replaceChildren();
      populateFields(studentForm, student); // populate student details in form fields

      // get student's parents from all parents
      const studentParents = allParents.filter((par) =>
        parents.includes(par.id),
      );

      if (studentParents.length) {
        studentParents.forEach((parent, i) => {
          const newParent = parentTemplate.content.cloneNode(true);
          const entry = newParent.querySelector(".parent-entry");
          entry.setAttribute("data-index", i);
          updateNameAttrForParent(entry, i); // update name attributes to parents[i]..
          const removeParentBtn = entry.querySelector(".remove-parent-btn");
          populateFields(entry, parent); // populate parent details in form fields
          entry.querySelector(".dedu-parent-guardian-top").remove();
          entry.querySelector("legend").textContent = parent.relationship;
          parentModeSwitch(newParent, "new", true);
          if (i > 0) removeParentBtn.classList.remove("hide-me");
          parentsContainer.appendChild(newParent);
        });
      }
      updateParentButtonState();
      updateUrlActionId(student.id);
      updateHiddenInput(student.id);
      showFormView();
    } else {
      alert("Error: " + result.data);
    }
  } catch (error) {
    console.error("Fetch error:", error);
  } finally {
  }
};

document.addEventListener("DOMContentLoaded", function () {
  function handleFiles(dropZone, files, fileInput) {
    // 1. Check if files exists and has at least one item
    if (!files || files.length === 0) {
      return; // Exit early if no file is selected
    }
    const file = files[0];

    const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    if (file.size > MAX_SIZE) {
      alert("File is too large. Max size is 2MB.");
      fileInput.value = ""; // Reset the input
      return;
    }

    // 2. Now it's safe to check the type
    if (file && file.type && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = (e) => updatePhoto(dropZone, e.target.result);
      reader.readAsDataURL(file);
    } else {
      alert("Please select a valid image file (JPG, PNG, or GIF).");
    }
  }

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  dropZones.forEach((dropZone) => {
    const fileInput = dropZone.querySelector('input[type="file"]');
    const removeBtn = dropZone.querySelector(".remove-img");

    // Handle File Selection
    fileInput.addEventListener("change", function (e) {
      handleFiles(dropZone, this.files, fileInput);
    });

    // Drag and Drop Logic
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      dropZone.addEventListener(eventName, preventDefaults, false);
    });

    dropZone.addEventListener("dragover", () =>
      dropZone.classList.add("drag-over"),
    );
    dropZone.addEventListener("dragleave", () =>
      dropZone.classList.remove("drag-over"),
    );

    dropZone.addEventListener("drop", (e) => {
      dropZone.classList.remove("drag-over");
      const files = e.dataTransfer.files;
      fileInput.files = files; // Sync drag-dropped file to the actual input
      handleFiles(dropZone, files, fileInput);
    });

    // Remove Image
    removeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      fileInput.value = ""; // Clear input
      updatePhoto(dropZone, "");
    });
  });
});

studentForm?.addEventListener("change", (e) => {
  if (e.target === classField) {
    const key = classField.value;
    let options = `<option value = "" disabled selected>-- select a class first --</option>`;
    if (key) {
      const hasSections = sections[key] && sections[key].length;
      const class_name = classes.find((cls) => cls.id === key).class_name;
      if (hasSections) {
        options = `<option value = "">All Sections</option>`;
        options += sections[key]
          .map(
            (sec) => `<option value = ${sec.id}>${sec.section_name}</option>`,
          )
          .join("");
      } else {
        options = `<option value = "" selected disabled>-- no sections for ${class_name} --</option>`;
      }
    }
    sectionsField.innerHTML = options;
  }
});

parentsContainer.addEventListener("click", function (e) {
  // Remove Parent Logic
  if (e.target && target(e, ".remove-parent-btn")) {
    const entry = target(e, ".parent-entry");
    const select = entry.querySelector(".existing-parent-selector select");
    selectedParents = selectedParents.filter((id) => id !== select.value);
    entry.remove();
    console.log("Selected Parents: ", selectedParents);
    updateParentButtonState();
  } else if (target(e, ".others")) {
    // if "others" is selected/clicked display input
    const input = target(e, ".rel-switch").querySelector(".radio-input");
    input.classList.remove("hide-me");
  } else if (
    !target(e, ".others") &&
    !e.target.classList.contains("radio-input")
  ) {
    //if click outside the input, it disappears
    parentsContainer.querySelectorAll(".radio-input").forEach((radioInput) => {
      if (radioInput.checkVisibility()) radioInput.classList.add("hide-me");
    });
  }
});

// 1. Handle Toggles and Removal via Event Delegation
parentsContainer.addEventListener("change", function (e) {
  if (e.target.matches(".parent-mode-switch")) {
    // toggle between "new"  & "existng" parent entry
    const parent = target(e, ".parent-entry");
    if (e.target.value === "existing") {
      parentModeSwitch(parent, "existing");
    } else if (e.target.value === "new") {
      parentModeSwitch(parent, "new");
    }
  } else if (
    e.target.matches(".input-rel") ||
    e.target.matches(".radio-input")
  ) {
    // parent-student relationship switch
    const parentNewTitle = target(e, ".parent-entry").querySelector("legend");
    const othersBtn = target(e, ".rel-switch").querySelector(".others-btn");
    const input = target(e, ".rel-switch").querySelector(".radio-input");
    if (["mother", "father"].includes(e.target.value)) {
      input.classList.add("hide-me");
      parentNewTitle.textContent = e.target.value.trim();
    } else if (e.target.matches(".radio-input")) {
      const othersLabel = othersBtn.nextElementSibling;
      othersBtn.value = e.target.value.trim().toLowerCase();
      othersLabel.textContent = e.target.value.trim();
      parentNewTitle.textContent = e.target.value.trim();
    } else if (e.target.matches(".others-btn")) {
      input.classList.remove("hide-me");
      input.focus();
    }
  } else if (e.target.matches(".existing-parent-selector select")) {
    updateParentOptions();
  }
});

// 2. Add New Parent Logic
addBtn.addEventListener("click", function () {
  const allEntries = parentsContainer.querySelectorAll(".parent-entry");

  if (allEntries.length < 3) {
    if (allEntries.length > 0) {
      const lastIndex = allEntries.length - 1;
      const requiredInputs = allEntries[lastIndex].querySelectorAll(
        ".existing-parent-selector select[required], .parent-fields input[required]",
      );
      const violator = [...requiredInputs].find((input) => !input.value);
      if (violator) {
        alert(
          "Please fill out the required fields before adding another parent.",
        );
        return;
      }
    }
    const newParent = parentTemplate.content.cloneNode(true);
    newParent
      .querySelector(".parent-entry")
      .setAttribute("data-index", allEntries.length);
    const removeParentBtn = newParent.querySelector(".remove-parent-btn");
    if (allEntries.length > 0) removeParentBtn.classList.remove("hide-me");
    console.log(removeParentBtn)
    const select = newParent.querySelector(".existing-parent-selector select");
    populateParentOptions(select, allParents);
    disableOptions(select, selectedParents, storedParents);
    newParent
      .querySelector(".dedu-parent-guardian-top")
      .classList.remove("hide-me");
    parentModeSwitch(newParent);
    parentsContainer.appendChild(newParent);    
    updateParentButtonState();
  }
});
