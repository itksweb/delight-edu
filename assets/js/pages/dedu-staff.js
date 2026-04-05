console.log("dedu-staff.js loaded");

const wpUser = document.querySelector("input[name='wp_user_id']");
const staffDbId = document.getElementById("staff_db_id");
const previewContainer = document.querySelector("#image-preview");
const previewImg = previewContainer.querySelector("img");
const removeBtn = previewContainer.querySelector(".remove-img");
const fname = document.querySelector('input[name="first_name"]');
const lname = document.querySelector('input[name="last_name"]');
const mname = document.querySelector('input[name="middle_name"]');
const email = document.querySelector('input[name="email"]');
const phone = document.querySelector('input[name="phone"]');
const position = document.querySelector('input[name="position"]');
const salaryAmount = document.querySelector('input[name="salary_amount"]');
const joiningDate = document.querySelector('input[name="joining_date"]');
const isTeacherToggle = document.getElementById("is_teacher_toggle");
const dob = document.querySelector('input[name="date_of_birth"]');
const roleSelect = document.querySelector('select[name="role_id"]');
const classId = document.querySelector('select[name="class_id"]');
const password = document.querySelector('input[type="password"]');
const academicFields = document.querySelectorAll("academic_field");

const updatePhoto = (src) => {
  previewImg.src = src ? src : "";
  if (src) previewContainer.classList.remove("hidden");
};

const ROLE_PERMISSIONS = deduStaffData.rolePermissions;

function syncPermissions(roleId, userOverrides = []) {
  const roleCaps = ROLE_PERMISSIONS[roleId] || [];
  const allCheckboxes = document.querySelectorAll(".staff-cap-checkbox");
  let ite = [];
  allCheckboxes.forEach((box) => {
    // Reset state
    box.checked = false;
    box.disabled = false;
    box.closest("label").style.opacity = "1";

    // 1. If it's in the ROLE, check and disable it
    if (roleCaps.includes(box.value) || userOverrides.includes(box.value)) {
      box.checked = true;
    }

    // 2. If it's in the USER overrides, check it (if not already handled by role)
    if (roleCaps.includes(box.value)) {
      ite = [...ite, box.value];
      box.disabled = true;
      box.closest("label").style.opacity = "0.7";
    }
  });
  // console.log("catch ya: ", ite);
}

// Add event listener for manual Role change
document
  .querySelector('select[name="role_id"]')
  .addEventListener("change", (e) => syncPermissions(e.target.value));

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;

  // 1. clear Basic Fields
  wpUser.value = "";
  staffDbId.value = "";
  fname.value = "";
  lname.value = "";
  mname.value = "";
  email.value = "";
  phone.value = "";
  position.value = "";
  salaryAmount.value = "";
  joiningDate.value = todaysDate(); //set default date to today's date
  roleSelect.value = "";
  classId.value = "";
  isTeacherToggle.checked = false;
  academicFields.forEach((el) => (el.style.display = "none"));

  updateUrlActionId();
  updateHiddenInput();
  showFormView();
};

const renderEditScreen = async (e) => {
  const ID = target(e, ".dedu-edit-icon").dataset.id;
  password.removeAttribute("required");

  // Prepare AJAX request
  const formData = new FormData();
  formData.append("action", "get_staff_details");
  formData.append("id", ID);
  formData.append("nonce", deduStaffData.nonce);

  try {
    const response = await fetch(ajaxurl, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      const data = result.data.staff;
      const perms = result.data.permissions;
      console.log("perms: ", perms);
      console.log("data: ", data);

      // 3. Populate Basic Fields
      formTitle.textContent = `Edit Staff: ${data.first_name} ${data.last_name}`;
      wpUser.value = data.wp_user_id;
      staffDbId.value = data.id;
      updatePhoto(data.photo_url);
      fname.value = data.first_name;
      mname.value = data.middle_name || "";
      lname.value = data.last_name;
      email.value = data.email;
      phone.value = data.phone || "";
      position.value = data.position || "";
      salaryAmount.value = data.salary_amount;
      joiningDate.value = data.joining_date;
      dob.value = data.date_of_birth;
      isTeacherToggle.checked = data.is_teacher == 1;
      classId.value = data.class_id || "";
      roleSelect.value = data.role_id;
      isTeacherToggle.checked
        ? academicFields.forEach((el) => (el.style.display = ""))
        : academicFields.forEach((el) => (el.style.display = "none"));
      submitBtn.textContent = "Update Staff";

      // Manually call the permission sync function
      syncPermissions(data.role_id, perms);
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
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("staff_photo");

  // Handle File Selection
  fileInput.addEventListener("change", function (e) {
    handleFiles(this.files);
  });

  // Drag and Drop Logic
  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

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
    handleFiles(files);
  });

  function handleFiles(files) {
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
      reader.onload = (e) => {
        updatePhoto(e.target.result);
        // previewImg.src = e.target.result;
        // previewContainer.classList.remove("hidden");
      };
      reader.readAsDataURL(file);
    } else {
      alert("Please select a valid image file (JPG, PNG, or GIF).");
    }
  }

  // Remove Image
  removeBtn.addEventListener("click", (e) => {
    e.preventDefault();
    fileInput.value = ""; // Clear input
    previewContainer.classList.add("hidden");
  });
});

const staffForm = document.querySelector("#staff-form");
const classField = document.querySelector("#class-field");
const sectionsField = document.querySelector("#sections-field");

staffForm?.addEventListener("change", (e) => {
  const isClass = classField.checkVisibility() && e.target === classField;
  if (isClass) {
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
  } else if (e.target === isTeacherToggle && !isTeacherToggle.checked) {
    classField.value = "";
    sectionsField.innerHTML = `<option value = "" disabled selected>-- select a class first --</option>`;
  }
});

const allPerms = document.querySelectorAll(".dedu-permission-card");
allPerms?.forEach((cont) => {
  const permBody = cont.querySelector(".dedu-cap-list");
  const permHead = cont.querySelector(".cap-list-head");
  const showHideToggle = cont.querySelector(".dedu-group-label");
  const checkAll = permHead.querySelector(".dedu-checkbox-label");
  const showHide = (action) => {
    permBody.style.display = action;
    checkAll.style.display = action;
    let togg = showHideToggle.querySelector(".dashicons");
    if (action === "none") {
      togg.classList.remove("dashicons-minus");
      togg.classList.add("dashicons-plus");
    } else {
       togg.classList.remove("dashicons-plus");
       togg.classList.add("dashicons-minus");
    }
  }
  showHide("none")
  
  
  showHideToggle.addEventListener("click", (e) => {
    showHide(permBody.checkVisibility() ? "none" : "flex");    
  });
  // --- 1. SELECT ALL CHECKBOXES ---
  checkUncheckAll(".check-all-caps", ".cap-checkbox", cont);
  // --- 2. INDIVIDUAL CHECKBOX LOGIC ---
  checkUncheckSingle(cont, ".check-all-caps", "cap-checkbox");
});

staffForm?.addEventListener("submit", () => {
  const checkboxes = staffForm.querySelectorAll(".staff-cap-checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.disabled = false;
  });
});
