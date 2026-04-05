console.log("dedu-classes-sections.js loaded");

const className = document.querySelector("#class_name");
const numericName = document.querySelector("#numeric_name");
const sectionsContainer = document.querySelector("#sections-container");
const sectionTemplate = document.querySelector("#section-template");
const addSectionBtn = document.querySelector("#add-section-btn");
const removeSectionBtn = document.querySelector("remove-section");

const renderAddNewScreen = () => {
  formTitle.textContent = `Add A New ${itemType}`;
  submitBtn.textContent = `Create ${itemType}`;
  className.value = "";
  numericName.value = "";
  sectionsContainer.replaceChildren();
  updateUrlActionId();
  updateHiddenInput();
  showFormView();
};


const addSection = (name = "", cat = "") => {
  const clone = sectionTemplate.content.cloneNode(true);
  clone.querySelector(".section-name").value = name;
  clone.querySelector(".section-category").value = cat;
  sectionsContainer.appendChild(clone);
};

document.addEventListener("click", (e) => {
  if (target(e, ".remove-section")) target(e, ".section-row").remove();
  else if (target(e, "#add-section-btn")) addSection();
})

const renderEditScreen = (e) => {
  const { name, num, sections, id } = target(e, ".dedu-edit-icon").dataset;
  formTitle.textContent = `Edit ${itemType}: ${name}`;
  submitBtn.textContent = `Update ${name}`;
  className.value = name;
  numericName.value = num;
  sectionsContainer.replaceChildren();
  const sectionList = sections.split(",").map((txt) => txt.trim());
  if (sectionList.length) sectionList.forEach((txt) => addSection(txt));
  updateUrlActionId(id);
  updateHiddenInput(id);
  showFormView();
};
