function toggleDropdown(button, content) {
  content.classList.toggle("show");
}

const optionsTrigger = document.querySelector("#multiSelectDropdownButton");
const multiSelectContent = document.querySelector(
  "#multiSelectDropdownContent",
);
const selectedMultiValuesSpan = document.querySelector("#selectedMultiValues");
const fieldValue = document.querySelector("#hiddenMultiSelectValue");
const multiSelectOptions = multiSelectContent.querySelectorAll(
  ".multi-select-option",
);

let selectedMultiValues = [];

function updateMultiSelectDisplay() {
  selectedMultiValuesSpan.innerHTML = "";
  if (selectedMultiValues.length === 0) {
    const placeholder = document.createElement("span");
    placeholder.classList.add("placeholder");
    placeholder.textContent = "Choose";
    selectedMultiValuesSpan.appendChild(placeholder);
  } else {
    selectedMultiValues.forEach((value) => {
      const optionElement = Array.from(multiSelectOptions).find(
        (opt) => opt.getAttribute("data-value") === value,
      );
      if (optionElement) {
        const tag = document.createElement("span");
        tag.classList.add("selected-tag");
        tag.textContent = optionElement.textContent.trim();
        selectedMultiValuesSpan.appendChild(tag);
      }
    });
  }
  fieldValue.value = selectedMultiValues.join(",");
}

optionsTrigger.addEventListener("click", () =>
  toggleDropdown(optionsTrigger, multiSelectContent),
);

multiSelectOptions.forEach((option) => {
  option.addEventListener("click", function (event) {
    event.preventDefault();
    const value = this.getAttribute("data-value");
    const checkboxIcon = this.querySelector(".checkbox-icon svg");

    if (this.classList.contains("selected")) {
      this.classList.remove("selected");
      checkboxIcon.style.display = "none";
      selectedMultiValues = selectedMultiValues.filter((v) => v !== value);
    } else {
      this.classList.add("selected");
      checkboxIcon.style.display = "block";
      selectedMultiValues.push(value);
    }
    updateMultiSelectDisplay();
  });
});

updateMultiSelectDisplay();

window.addEventListener("click", function (event) {
  if (
    !optionsTrigger.contains(event.target) &&
    multiSelectContent.classList.contains("show")
  ) {
    multiSelectContent.classList.remove("show");
  }
});

window.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    if (multiSelectContent.classList.contains("show")) {
      multiSelectContent.classList.remove("show");
    }
  }
});
