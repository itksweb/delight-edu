console.log("dedu-subjects.js loaded");

const subjectName = document.querySelector("#subject_name");
const subjectType = document.querySelector("#subject_type");


const renderAddNewScreen = () => {
  console.log("i am here");
  subjectName.value = "";
  subjectType.value = "core";
};

const renderEditScreen = (data) => {
  const { name, type } = data;
  subjectName.value = name;
  subjectType.value = type;
};
