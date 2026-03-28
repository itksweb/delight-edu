console.log("dedu-subjects.js loaded");

const subjectName = document.querySelector("#subject_name");
const subjectType = document.querySelector("#subject_type");

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Add ${itemType}`;
  console.log("i am here");
  subjectName.value = "";
  subjectType.value = "core";
};

const renderEditScreen = (data) => {
  const { name, type } = data;
  formTitle.textContent = `Edit ${itemType}: ${name}`;
  submitBtn.textContent = `Update ${itemType}`;
  subjectName.value = name;
  subjectType.value = type;
};
