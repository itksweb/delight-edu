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

  dropZones.forEach(dropZone => {
    console.log("wetin", dropZone);
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
  })
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

