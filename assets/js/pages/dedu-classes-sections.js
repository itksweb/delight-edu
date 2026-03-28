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
};

const removeSection = (e) => {
  if (e.target.closest(".remove-section")) e.target.parentElement.remove();
};
const addSection = (name = "", cat = "") => {
  const clone = sectionTemplate.content.cloneNode(true);
  clone.querySelector(".section-name").value = name;
  clone.querySelector(".section-category").value = cat;
  sectionsContainer.appendChild(clone);
};

addSectionBtn.addEventListener("click", addSection);
sectionsContainer.addEventListener("click", removeSection);

const renderEditScreen = (data) => {
  const { name, num, sections, id } = data;
  formTitle.textContent = `Edit ${itemType}: ${name}`;
  submitBtn.textContent = `Update ${name}`;
  className.value = name;
  numericName.value = num;
  sectionsContainer.replaceChildren();
  const sectionList = sections.split(",").map((txt) => txt.trim());
  if (sectionList.length) {
    sectionList.forEach((txt) => addSection(txt));
  }
};
