console.log("dedu-roles.js loaded");
const roleName = document.querySelector("#role_name");
const permsContainer = document.querySelector(".dedu-permissions-grid");
const permissions = permsContainer.querySelectorAll("input[type='checkbox']");
const allPerms = document.querySelectorAll(".dedu-permission-card");


const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;
  roleName.value = "";
  permissions.forEach((cb) => (cb.checked = false));
  updateUrlActionId();
  updateHiddenInput();
  showFormView();
  allPerms?.forEach((cont) => displayPermissionsGroup("none", cont));
};

const renderEditScreen = (e) => {
  const { id, name } = target(e, ".dedu-edit-icon").dataset;
  formView.querySelector("h3").textContent = `Edit ${itemType}: ${name}`;
  submitBtn.textContent = `Update ${itemType}`;

  roleName.value = name;
  updateUrlActionId(id);
  updateHiddenInput(id);
  syncPermissions(id);
  console.log("hey!");
  showFormView();
  allPerms?.forEach((cont) => {
    const hasCheck = cont.querySelector(".cap-checkbox:checked");
    displayPermissionsGroup(hasCheck ? "flex" : "none", cont);
  });
};


allPerms?.forEach((cont) => {
  const permBody = cont.querySelector(".dedu-cap-list");
  const showHideToggle = cont.querySelector(".dedu-group-label");

  showHideToggle.addEventListener("click", (e) => {
    displayPermissionsGroup(permBody.checkVisibility() ? "none" : "flex", cont);
  });
  // --- 1. SELECT ALL CHECKBOXES ---
  checkUncheckAll(".check-all-caps", ".cap-checkbox", cont);
  // --- 2. INDIVIDUAL CHECKBOX LOGIC ---
  checkUncheckSingle(cont, ".check-all-caps", "cap-checkbox");
});
