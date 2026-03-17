console.log("dedu-roles.js loaded");
const formTitle = document.querySelector("#dedu-form-view h3");
const submitBtn = document
  .querySelector("form")
  .querySelector('button[type="submit"]');
const roleName = document.querySelector("#role_name");
const permsContainer = document.querySelector(".dedu-permissions-grid");
const permissions = permsContainer.querySelectorAll("input[type='checkbox']");
let ppp = document.querySelector(
  ".dedu-permissions-grid input[type='checkbox']"
);



const renderAddNewScreen = () => {
  roleName.value = "";
  permissions.forEach((cb) => (cb.checked = false));
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

const renderEditScreen = (data) => {
  const { name, caps } = data;
  permissions.forEach((cb) => (cb.checked = false));
  roleName.value = name;
  prefillCheckboxes(caps);
};

const deduDeleteOne = (data) => {

}
