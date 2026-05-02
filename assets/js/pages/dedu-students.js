console.log("dedu-student.js loaded");

const wpUser = document.querySelector("input[name='wp_user_id']");
const fname = document.querySelector('input[name="first_name"]');
const lname = document.querySelector('input[name="last_name"]');
const mname = document.querySelector('input[name="middle_name"]');
const email = document.querySelector('input[name="email"]');
const phone = document.querySelector('input[name="phone"]');
const joiningDate = document.querySelector('input[name="joining_date"]');
const dob = document.querySelector('input[name="date_of_birth"]');
const classId = document.querySelector('select[name="class_id"]');
const password = document.querySelector('input[type="password"]');
const dropZones = document.querySelectorAll(".dedu-upload-container");

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;

  // 1. clear Basic Fields
  wpUser.value = "";
  // studentDbId.value = "";
  fname.value = "";
  lname.value = "";
  mname.value = "";
  email.value = "";
  phone.value = "";
  joiningDate.value = todaysDate(); //set default date to today's date
  classId.value = "";

  updateUrlActionId();
  updateHiddenInput();
  showFormView();
};

const renderEditScreen = async (e) => {
  const ID = target(e, ".dedu-edit-icon").dataset.id;
  password.removeAttribute("required");

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
      const data = result.data.student;
      console.log("data: ", data);

      // Populate Basic Fields
      formTitle.textContent = `Edit student: ${data.first_name} ${data.last_name}`;
      wpUser.value = data.wp_user_id;
      // updatePhoto(data.photo_url);
      fname.value = data.first_name;
      mname.value = data.middle_name || "";
      lname.value = data.last_name;
      email.value = data.email;
      phone.value = data.phone || "";
      joiningDate.value = data.joining_date;
      dob.value = data.date_of_birth;
      classId.value = data.class_id || "";
      submitBtn.textContent = "Update student";

      updateUrlActionId(data.id);
      updateHiddenInput(data.id);
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
  const updatePhoto = (dropZone, src = "") => {
    // console.log("wetin", dropZone)
    const previewContainer = dropZone.querySelector(".image-preview");
    const previewImg = previewContainer.querySelector("img");
    previewImg.src = src;
    if (src) {
      previewContainer.classList.remove("hidden");
      dropZone.classList.remove("image-upload");
      dropZone.querySelector("p").classList.add("hidden");
      dropZone.querySelector(".upload-icon").classList.add("hidden");
    } else {
      previewContainer.classList.add("hidden");
      dropZone.classList.add("image-upload");
      dropZone.querySelector("p").classList.remove("hidden");
      dropZone.querySelector(".upload-icon").classList.remove("hidden");
    }
  };

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
      dropZone.classList.add("drag-over")
    );
    dropZone.addEventListener("dragleave", () =>
      dropZone.classList.remove("drag-over")
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
            (sec) => `<option value = ${sec.id}>${sec.section_name}</option>`
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
  const parentsContainer = document.getElementById("parents-list");
  const addBtn = document.getElementById("add-parent-btn");
  let parentCount = 1;

  // 1. Handle Toggles and Removal via Event Delegation
  parentsContainer.addEventListener("change", function (e) {
    // Toggle Logic: Existing vs New
    if (e.target && e.target.classList.contains("parent-mode-switch")) {
      const wrapper = e.target.closest(".parent-entry");
      const newFields = wrapper.querySelector(".parent-fields");
      const existingSelector = wrapper.querySelector(
        ".existing-parent-selector"
      );
      const requiredInputs = newFields.querySelectorAll(".parent-required");

      if (e.target.value === "existing") {
        newFields.classList.add("hide-me");
        existingSelector.classList.remove("hide-me");
        requiredInputs.forEach((input) => (input.required = false));
      } else {
        newFields.classList.remove("hide-me");
        existingSelector.classList.add("hide-me");
        requiredInputs.forEach((input) => (input.required = true));
      }
    } else if (["father", "mother", "others"].includes(e.target.id)) {
      const input = e.target.closest(".unit").querySelector(".radio-input");
      if (e.target.id === "others") {
        input?.classList.remove("hide-me");
        input?.focus();
      } else {
        input.classList.add("hide-me");
      }
    } else if (e.target.matches(".radio-input")) {
      const othersBtn = target(e, ".unit").querySelector("#others");
      const othersLabel = othersBtn.nextElementSibling;
      if (e.target.checkVisibility()) {
        othersBtn.value = e.target.value.trim().toLowerCase();
        othersLabel.textContent = e.target.value.trim();
      }
   
    }
  });

  parentsContainer.addEventListener("click", function (e) {
    // Remove Parent Logic
    if (e.target && e.target.closest(".remove-parent-btn")) {
      const row = e.target.closest(".parent-entry-wrapper");
      row.remove();
      parentCount--;
      updateParentButtonState();
    } else if (target(e, ".others")) {
      const input = target(e, ".unit").querySelector(".radio-input");
      input.classList.remove("hide-me");
    } else if (!target(e, ".others") && !e.target.classList.contains("radio-input")) {
      parentsContainer
        .querySelectorAll(".radio-input")
        .forEach((radioInput) => {
          if (radioInput.checkVisibility()) radioInput.classList.add("hide-me");
        });
    }
  });

  // 2. Add New Parent Logic
  addBtn.addEventListener("click", function () {
    if (parentCount >= 3) {
      alert("Maximum of 3 parents/guardians allowed.");
      return;
    }

    const firstParent = document.querySelector(".parent-entry-wrapper");
    const newParent = firstParent.cloneNode(true);
    const index = parentCount;

    // Reset and Update Inputs
    newParent.setAttribute("data-index", index);

    const inputs = newParent.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      // Update the array index in the name attribute: parents[0] -> parents[1]
      if (input.name) {
        input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
      }

      // Reset values except for the radio buttons
      if (input.type !== "radio") {
        input.value = "";
      } else {
        // Ensure radio button names are unique to their group
        input.checked = input.value === "new";
      }
    });

    // Clean up UI state for the clone
    newParent.querySelector(".remove-parent-btn").classList.remove("hide-me");
    newParent.querySelector(".parent-fields").classList.remove("hide-me");
    newParent
      .querySelector(".existing-parent-selector")
      .classList.add("hide-me");

    parentsContainer.appendChild(newParent);
    parentCount++;
    updateParentButtonState();
  });

  function updateParentButtonState() {
    // Optional: Disable add button if 3 reached
    addBtn.disabled = parentCount >= 3;
  }
});
