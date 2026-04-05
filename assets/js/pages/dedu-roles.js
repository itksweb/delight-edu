console.log("dedu-roles.js loaded");
const roleName = document.querySelector("#role_name");
const permsContainer = document.querySelector(".dedu-permissions-grid");
const permissions = permsContainer.querySelectorAll("input[type='checkbox']");
let ppp = document.querySelector(
  ".dedu-permissions-grid input[type='checkbox']"
);

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Create ${itemType}`;
  roleName.value = "";
  permissions.forEach((cb) => (cb.checked = false));
  updateUrlActionId();
  updateHiddenInput();
  showFormView();
};

const prefillCheckboxes = (caps = "", roles = []) => {
  if (!caps) return;
  const capsArray = caps.split(",").map((c) => c.trim());
  console.log("hyy ", capsArray);
  capsArray.forEach((cap) => {
    const el = permsContainer.querySelector(`input[value=${cap}]`);
    el.checked = true;
  });
};

const renderEditScreen = (e) => {
  const { id, name, caps } = target(e, ".dedu-edit-icon").dataset;
  document.querySelector(
    "#dedu-form-view h3"
  ).textContent = `Edit ${itemType}: ${name}`;
  document.querySelector(
    '#dedu-form-view button[type="submit"]'
  ).textContent = `Update ${itemType}`;

  permissions.forEach((cb) => (cb.checked = false));
  roleName.value = name;
  prefillCheckboxes(caps);

  updateUrlActionId(id);
  updateHiddenInput(id);
  showFormView();
};

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
  };
  showHide("none");

  showHideToggle.addEventListener("click", (e) => {
    showHide(permBody.checkVisibility() ? "none" : "flex");
  });
  // --- 1. SELECT ALL CHECKBOXES ---
  checkUncheckAll(".check-all-caps", ".cap-checkbox", cont);
  // --- 2. INDIVIDUAL CHECKBOX LOGIC ---
  checkUncheckSingle(cont, ".check-all-caps", "cap-checkbox");
});


