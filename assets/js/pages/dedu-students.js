console.log("dedu-student.js loaded");
const parentsContainer = document.getElementById("parents-list");
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
const allParents = deduStudentData["all_parents"];
let mode = "";

const parentSelect = parentsContainer.querySelector(
  ".existing-parent-selector select",
);

//populate parents options
allParents.forEach((parent) => {
  const text = `${parent["first_name"]} ${parent["last_name"]}`;
  const opt = creatIt("option", "", text);
  opt.setAttribute("value", parent.id);
  parentSelect.appendChild(opt);
});

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

const resetInputs = (par = document, i = 0) => {
  const inputs = par.querySelectorAll("input, select, textarea");
  inputs.forEach((input) => {
    // Reset input values
    if (["radio", "checkbox"].includes(input.type)) {
      input.checked = false;
    } else if (input.type !== "hidden") {
      input.value = "";
    }

    if (par !== document && par.matches(".parent-entry")) {
      par.querySelector("legend").textContent = "Parent Detail";
    }

    // Update the array index in the name attribute: parents[0] -> parents[1]
    const regex = /^parents\[\d+\]/;
    if (input.name && regex.test(input.name)) {
      input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
    }
  });
};

const parentModeSwitch = (par, mode = "") => {
  const newFields = par.querySelector(".parent-fields");
  const relationshipToggle = par.querySelector(".relationship-toggle");
  const existingSelector = par.querySelector(".existing-parent-selector");
  const existingParent = existingSelector.querySelector("select");
  const requiredInputs = newFields.querySelectorAll(".parent-required");
  const newExistingToggle = par.querySelector(".parent-mode-toggle")

  if (mode === "new") {
    existingSelector.classList.add("hide-me");
    newFields.classList.remove("hide-me");
    relationshipToggle.classList.remove("hide-me");
    existingParent.required = false;
    existingParent.value = "";
    requiredInputs.forEach((input) => (input.required = true));
    newExistingToggle.classList.remove("wide");
    par.querySelector("legend").textContent = "Parent Details"
  } else if (mode === "existing") {
    newFields.classList.add("hide-me");
    existingSelector.classList.remove("hide-me");
    relationshipToggle.classList.add("hide-me");
    existingParent.required = true;
    requiredInputs.forEach((input) => (input.required = false));
    newExistingToggle.classList.remove("wide");
    newFields
      .querySelectorAll("input, select, textarea")
      .forEach((field) => (field.value = ""));
  } else if (!mode) {
    existingSelector.classList.add("hide-me");
    newFields.classList.add("hide-me");
    relationshipToggle.classList.add("hide-me");
    newExistingToggle.classList.add("wide");
  }
};

const displayRemoveBtn = (par, mode = "show") => {
  mode === "show"
    ? par.querySelector(".remove-parent-btn").classList.remove("hide-me")
    : par.querySelector(".remove-parent-btn").classList.add("hide-me");
};

const displayParGuardTop = (par, mode = "show") => {
  mode === "show"
    ? par.querySelector(".dedu-parent-guardian-top").classList.remove("hide-me")
    : par.querySelector(".dedu-parent-guardian-top").classList.add("hide-me");
};

const populateParentFields = (par, parent) => {
  par.querySelector("legend").textContent = parent["relationship"]
    ? parent["relationship"]
    : "";
  const dropZone = par.querySelector(".sub-pix");
  dropZone.style.transform = "translateY(10px)";
  const parentInputs = par.querySelectorAll("input, select, textarea");
  parentInputs.forEach((input) => {
    if (input.name) {
      const key = input.name.split("][")[1].slice(0, -1);
      if (key !== "profile_photo") {
        input.value = parent[key] ? parent[key] : "";
      } else if (key === "profile_photo" && parent[key]) {
        updatePhoto(dropZone, parent[key]);
      }
    }
  });
};

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;

  wpUser.value = "";
  joiningDate.value = todaysDate(); //set default date to today's date
  clearUnwantedParentEntries();
  resetInputs(); // clear/reset fields
  firstParent
    .querySelector(".dedu-parent-guardian-top")
    .classList.remove("hide-me");
  firstParent.querySelector(".remove-parent-btn").classList.add("hide-me");
  parentModeSwitch(firstParent);
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

      clearUnwantedParentEntries();
      resetInputs(); // clear/reset fields
      firstParent.querySelector(".remove-parent-btn").classList.add("hide-me");

      // Populate Basic Fields
      formTitle.textContent = `Edit student: ${student.first_name} ${student.last_name}`;
      wpUser.value = student.user_id;
      updatePhoto(pixZone, student.photo_url);
      fname.value = student.first_name;
      mname.value = student.middle_name || "";
      lname.value = student.last_name;
      email.value = student.email;
      phone.value = student.phone || "";
      admissionNumber.value = student["admission_no"] || "";
      joiningDate.value = student.joining_date;
      dob.value = student.date_of_birth;
      classId.value = student.class_id;
      sectionId.value = student.section_id || "";
      address.value = student.address || "";
      submitBtn.textContent = "Update student";

      // get student's parents from all parents
      const studentParents = allParents.filter((par) =>
        parents.includes(par.id),
      );

      if (studentParents.length) {
        firstParent
          ?.querySelector(".dedu-parent-guardian-top")
          .classList.add("hide-me");

        studentParents.forEach((parent, i) => {
          if (+firstParent.dataset.index === i) {
            parentModeSwitch(firstParent, "new");
            populateParentFields(firstParent, parent);
          } else {
            const newParent = firstParent.cloneNode(true);
            resetInputs(newParent, i);
            populateParentFields(newParent, parent);
            parentsContainer.appendChild(newParent);
          }
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
    // console.log("wetin", dropZone);
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

const studentForm = document.querySelector("#student-form");
const classField = document.querySelector("#class-field");
const sectionsField = document.querySelector("#sections-field");

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

document.addEventListener("DOMContentLoaded", function () {
  
});

// 1. Handle Toggles and Removal via Event Delegation
  parentsContainer.addEventListener("change", function (e) {
    // Toggle Logic: Existing vs New
    if (e.target && e.target.classList.contains("parent-mode-switch")) {
      const parent = target(e, ".parent-entry");
      
      const index = parent.dataset.index;
      console.log(index);
      if (e.target.value === "existing") parentModeSwitch(parent, "existing");
      else if (e.target.value === "new") parentModeSwitch(parent, "new");
    } else if (["father", "mother", "others"].includes(e.target.id)) {
      const tit = target(e, ".parent-entry").querySelector("legend");
      const input = target(e, ".relationship-toggle").querySelector(
        ".radio-input",
      );
      if (e.target.id === "others") {
        input?.classList.remove("hide-me");
        input?.focus();
      } else {
        input.classList.add("hide-me");
        tit.textContent = e.target.value.trim();
      }
    } else if (e.target.matches(".radio-input")) {
      const othersBtn = target(e, ".relationship-toggle").querySelector(
        "#others",
      );
      const othersLabel = othersBtn.nextElementSibling;
      if (e.target.checkVisibility()) {
        othersBtn.value = e.target.value.trim().toLowerCase();
        othersLabel.textContent = e.target.value.trim();
        const tit = target(e, ".parent-entry").querySelector("legend");
        tit.textContent = e.target.value.trim();
      }
    }
  });

  parentsContainer.addEventListener("click", function (e) {
    // Remove Parent Logic
    if (e.target && e.target.closest(".remove-parent-btn")) {
      e.target.closest(".parent-entry").remove();
      updateParentButtonState();
    } else if (target(e, ".others")) {
      // if "others" is selected/clicked display input
      const input = target(e, ".relationship-toggle").querySelector(
        ".radio-input",
      );
      input.classList.remove("hide-me");
    } else if (
      !target(e, ".others") &&
      !e.target.classList.contains("radio-input")
    ) {
      //if click outside the input, it disappears
      parentsContainer
        .querySelectorAll(".radio-input")
        .forEach((radioInput) => {
          if (radioInput.checkVisibility()) radioInput.classList.add("hide-me");
        });
    }
  });

  // 2. Add New Parent Logic
  addBtn.addEventListener("click", function () {
    const allEntries = parentsContainer.querySelectorAll(".parent-entry");

    if (allEntries.length < 3) {
      const lastIndex = allEntries.length - 1;
      const requiredInputs = allEntries[lastIndex].querySelectorAll(
        ".existing-parent-selector select[required], .parent-fields input[required]",
      );
      // console.log(requiredInputs);
      const violator = [...requiredInputs].find((input) => !input.value);
      if (violator) {
        console.log(violator);
        alert(
          "Please fill out the required fields in the present parent form before adding another one.",
        );
        return;
      }

      const newParent = firstParent.cloneNode(true);
      resetInputs(newParent, allEntries.length); // Reset and Update Inputs
      newParent.setAttribute("data-index", allEntries.length);
      parentModeSwitch(newParent);
      newParent
        .querySelector(".dedu-parent-guardian-top")
        .classList.remove("hide-me");
      newParent.querySelector(".remove-parent-btn").classList.remove("hide-me");
      parentsContainer.appendChild(newParent);
      updateParentButtonState();
    }
  });

`
Next step: remove already selected parents from the select options available so that the user doesn't select the same parents multiple times  

`