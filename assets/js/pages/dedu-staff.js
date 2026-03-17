console.log("dedu-staff.js loaded");
const formTitle = document.querySelector("#dedu-form-view h3");
const submitBtn = document
  .querySelector("form")
  .querySelector('button[type="submit"]');


const staffDbId = document.getElementById("staff_db_id");
const fname = document.querySelector('input[name="first_name"]');
const lname = document.querySelector('input[name="last_name"]');
const mname = (document.querySelector('input[name="middle_name"]'));
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
const academicFields = document.getElementById("academic_fields");

const ROLE_PERMISSIONS = deduStaffData.rolePermissions;

const todaysDate = () => {
  const date = new Date();
  return date.toISOString().split("T")[0];
};

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
  formTitle.textContent = "Add New Staff";
  submitBtn.textContent = "Add Staff";
  // 1. clear Basic Fields
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
  academicFields.style.display = isTeacherToggle.checked ? "block" : "none";
  
  // Trigger the toggle visual logic manually
  
  
};

const renderEditScreen = async (data) => {
  password.removeAttribute("required");

  // Prepare AJAX request
  const formData = new FormData();
  formData.append("action", "get_staff_details");
  formData.append("id", data.id);
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
      staffDbId.value = data.id;
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
      academicFields.style.display = isTeacherToggle.checked ? "block" : "none";
      submitBtn.textContent = "Update Staff";

      // Manually call the permission sync function
      syncPermissions(data.role_id, perms);
    } else {
      alert("Error: " + result.data);
    }
  } catch (error) {
    console.error("Fetch error:", error);
  } finally {
  }
};
