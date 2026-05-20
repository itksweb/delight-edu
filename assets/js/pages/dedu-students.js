console.log("dedu-student.js loaded");
const parentsContainer = document.getElementById("parents-list");
const parentTemplate = document.querySelector("#entry");
const wpUser = document.querySelector("input[name='wp_user_id']");
const joiningDate = document.querySelector("input[name='joining_date']");
const pixZone = document.querySelector(".dedu-upload-container:not(.sub-pix)");

const addBtn = document.getElementById("add-parent-btn");
const studentForm = document.querySelector("#student-form");
const classField = document.querySelector("#class-field");
const sectionsField = document.querySelector("#sections-field");

const dropZones = document.querySelectorAll(".dedu-upload-container");
const classes = deduStudentData["classes"];
const sections = deduStudentData["sections"];
const allParents = deduStudentData["all_parents"];
let selectedParents = [];
let storedParents = [];
let sectionId = null;

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
  allEntries.forEach((select) =>
    disableOptions(select, selectedParents, storedParents),
  );
  console.log("Selected Parents: ", selectedParents);
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

const prepareImageUploader = (dropZone) => {
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
};

// Update the array index in the name attribute: parents[0] -> parents[1]
const updateNameAttrForParent = (par, i) => {
  const inputs = par.querySelectorAll("input, select, textarea");
  const regex = /^parents\[\d+\]/;
  inputs.forEach((input) => {
    if (input.name && regex.test(input.name)) {
      input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
      if (input.type === "file") {
        input
          .closest("label")
          .setAttribute("for", `parents[${i}][profile_photo]`);
        input.setAttribute("id", `parents[${i}][profile_photo]`);
      }
    }
  });
};

const parentModeSwitch = (par, mode = "") => {
  const newFields = par.querySelector(".parent-fields");
  const requiredInputs = newFields.querySelectorAll(".parent-required");

  if (mode === "fromDb") {
    newFields.classList.remove("hide-me");
    requiredInputs.forEach((input) => (input.required = true));
  } else {
    const newExistingToggle = par.querySelector(".parent-mode-toggle");
    const guardianTop = par.querySelector(".dedu-parent-guardian-top");
    const relationshipToggle = par.querySelector(".rel-switch");
    const relInputs = relationshipToggle.querySelectorAll(".input-rel");
    const existingSelector = par.querySelector(".existing-parent-selector");
    const existingParent = existingSelector.querySelector("select");
    if (mode === "new") {
      existingSelector.classList.add("hide-me");
      newFields.classList.remove("hide-me");
      relationshipToggle.classList.remove("hide-me");
      existingParent.required = false;
      existingParent.value = "";
      requiredInputs.forEach((input) => (input.required = true));
      relInputs.forEach((input) => (input.required = true));
      newExistingToggle.classList.remove("wide");
      par.querySelector("legend").textContent = "Parent Details";
    } else if (mode === "existing") {
      newFields.classList.add("hide-me");
      existingSelector.classList.remove("hide-me");
      relationshipToggle.classList.add("hide-me");
      requiredInputs.forEach((input) => (input.required = false));
      relInputs.forEach((input) => (input.required = false));
      existingParent.required = true;
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

const buildOptionsAndPickOne = (classId = null, sectionId = null) => {
  let options = `<option value = "" disabled selected>-- select a class first --</option>`;
  if (classId) {
    const hasSections = sections[classId] && sections[classId].length;
    const class_name = classes.find((cls) => cls.id === classId).class_name;
    console.log("");
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

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;
  wpUser.value = "";
  storedParents = [];
  selectedParents = [];

  parentsContainer.replaceChildren(); // clear all parent entries
  populateFields(studentForm); // clear all fields
  joiningDate.value = todaysDate(); //set default date to today's date
  prepareImageUploader(pixZone);

  // Add one parent entry by default in "new" mode
  const newParent = parentTemplate.content.cloneNode(true);
  newParent.querySelector(".parent-entry").setAttribute("data-index", 0);
  const select = newParent.querySelector(".existing-parent-selector select");
  const dropZone = newParent.querySelector(".dedu-upload-container");
  prepareImageUploader(dropZone);
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
      sectionId = student.section_id; // store section_id for later use in section options population

      // Populate Basic Fields
      formTitle.textContent = `Edit student: ${student.first_name} ${student.last_name}`;
      submitBtn.textContent = "Update student";
      updatePhoto(pixZone, student.photo_url);
      parentsContainer.replaceChildren();
      populateFields(studentForm, student); // populate student details in form fields
      buildOptionsAndPickOne(student.class_id, student.section_id); // populate section options based on class and pick the right one

      // get student's parents from all parents
      const studentParents = allParents.filter((par) =>
        parents.includes(par.id),
      );

      if (studentParents.length) {
        // Create your new hidden input element for parent_id and append it to the parentFields container
        const createHiddenInput = (parent, i) => {
          const hiddenInput = document.createElement("input");
          hiddenInput.type = "hidden";
          hiddenInput.name = `parents[${i}][parent_id]`;
          hiddenInput.value = parent.id; // Or dynamically assign an ID if needed
          return hiddenInput;
        };

        studentParents.forEach((parent, i) => {
          const newParent = parentTemplate.content.cloneNode(true);
          const entry = newParent.querySelector(".parent-entry");
          entry.setAttribute("data-index", i);
          updateNameAttrForParent(entry, i); // update name attributes to parents[i]..
          const removeParentBtn = entry.querySelector(".remove-parent-btn");
          const dropZone = entry.querySelector(".dedu-upload-container");
          prepareImageUploader(dropZone);
          populateFields(entry, parent); // populate parent details in form fields
          entry.querySelector(".dedu-parent-guardian-top").remove();
          entry.querySelector("legend").textContent = parent.relationship;
          const parentFields = entry.querySelector(".parent-fields");
          parentFields.prepend(createHiddenInput(parent, i)); // add hidden input for parent_id
          parentModeSwitch(newParent, "fromDb");
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

// 1. Handle Toggles and Removal via Event Delegation
document.addEventListener("change", function (e) {
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
  } else if (e.target === classField) {
    buildOptionsAndPickOne(classField.value, sectionId);
  }
});

parentsContainer.addEventListener("click", function (e) {
  // Remove Parent Logic
  if (e.target && target(e, ".remove-parent-btn")) {
    const entry = target(e, ".parent-entry");
    const select = entry.querySelector(".existing-parent-selector select");
    selectedParents = selectedParents.filter((id) => id !== select.value);
    entry.remove();
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

// 2. Add New Parent Logic
addBtn.addEventListener("click", function () {
  const allEntries = parentsContainer.querySelectorAll(".parent-entry");

  if (allEntries.length < 3) {
    if (allEntries.length > 0) {
      const lastIndex = allEntries.length - 1;
      const prevEntry = allEntries[lastIndex];
      const formTop = prevEntry.querySelector(".dedu-parent-guardian-top");
      if (formTop) {
        const requiredSelect = formTop.querySelector(".existing-parent-selector select[required]");
        if (requiredSelect && !requiredSelect.value) {
          alert("Please select an existing parent before adding another.");
          return;
        }
        const relSelected = prevEntry.querySelector(
          ".rel-switch input[type='radio']:checked",
        );
        const relRequired = prevEntry.querySelector(
          ".rel-switch input[type='radio']",
        ).required;
        if (relRequired && !relSelected) {
          alert("Please specify the relationship for the new parent before adding another.");
          return;
        }
      }

      const requiredInputs = prevEntry.querySelectorAll(
        ".parent-fields input[required]"
      );
      ;
      if ([...requiredInputs].find((input) => !input.value)) {
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
    updateNameAttrForParent(newParent, allEntries.length); // update name attributes to parents[i]..
    const removeParentBtn = newParent.querySelector(".remove-parent-btn");
    if (allEntries.length > 0) removeParentBtn.classList.remove("hide-me");
    const select = newParent.querySelector(".existing-parent-selector select");
    populateParentOptions(select, allParents);
    disableOptions(select, selectedParents, storedParents);
    newParent
      .querySelector(".dedu-parent-guardian-top")
      .classList.remove("hide-me");
    parentModeSwitch(newParent);
    const dropZone = newParent.querySelector(".dedu-upload-container");
    prepareImageUploader(dropZone);
    parentsContainer.appendChild(newParent);
    updateParentButtonState();
  }
});

document.querySelector("form").addEventListener("submit", function (e) {
  // Stop the form from redirecting/refreshing the page immediately
  e.preventDefault();

  // 1. Gather all the inputs from the form
  const formData = new FormData(this);

  // 2. Convert it to a clean object for the console
  const formProps = Object.fromEntries(formData);

  console.log("--- FORM DATA SUBMITTING ---");
  console.dir(formProps);

  // Optional: If you are using standard AJAX, you can trigger it here:
  // myAjaxSubmitFunction(formData);
});