//HELPER FUNCTIONS

function toggleDropdown(button, content) {
  content.classList.toggle("show");
}

const creatIt = (tag = "span", cls = "", text = "") => {
  const element = document.createElement(tag);
  if (cls) element.classList.add(cls);
  if (text) element.textContent = text;
  return element;
};

const allMultiSelects = document.querySelectorAll(".multi-select");
const checkOption = `<svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="feather feather-check"
                    style="display: none"
                >
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>`;
const selectToggles = allMultiSelects.querySelectorAll(".multi-select-button");


function buildDropdownOptions(select, ul) {
    ul.innerHTML = "";

    [...select.options].forEach((opt, index) => {
        const li = creatIt("li", "multi-select-option");
        const span1 = creatIt();
        span1.textContent = opt.text;
        li.dataset.value = opt.value;
        const span2 = creatIt("span", "checkbox-icon");
        span2.innerHTML = checkOption
        li.setAttribute("role", "option");
        li.setAttribute("aria-selected", opt.selected);
        li.appendChild(span1);
        li.appendChild(span2);
        ul.appendChild(li);
    });
}

function updateMultiSelectDisplay(selecte, label, itemOptions) {
    const values = [...selecte.selectedOptions].map(opt => opt.value);
    console.log(values);
    label.innerHTML = "";
    if (values.length === 0) {
        const placeholder = creatIt("span", "placeholder", "choose");
        label.appendChild(placeholder);
    } else {
        values.forEach(value => {
            const optionElement = [...itemOptions].find(
                opt => opt.dataset.value === value
            );
            if (optionElement) {
                const tag = creatIt(
                    "span",
                    "selected-tag",
                    optionElement.textContent.trim()
                );
                label.appendChild(tag);
            }
        });
    }
}

const handleOptionsSelect = (itemOptions, label, selecte) => {
    itemOptions.forEach(option => {
        option.addEventListener("click", function (event) {
            event.preventDefault();
            const value = option.dataset.value;
            console.log(value);
            const checkboxIcon = this.querySelector(".checkbox-icon svg");
            const selOpt = selecte.querySelector(`option[value="${value}"]`);
            selOpt.selected = !selOpt.selected;

            if (option.classList.contains("selected")) {
                option.classList.remove("selected");
                checkboxIcon.style.display = "none";
            } else {
                option.classList.add("selected");
                checkboxIcon.style.display = "block";
            }
            updateMultiSelectDisplay(selecte, label, itemOptions);
        });
    });
};





//Build custom dropdown options
allMultiSelects.forEach(item => {
    const select = item.querySelector("select");
    const ul = item.querySelector("ul");
    buildDropdownOptions(select, ul);
});

//setup dropdown toggle
selectToggles.forEach((btn) => {
  btn.addEventListener("click", (e) => {
    const par = e.target.closest(".multi-select");
    const content = par.querySelector(".dropdown-content");
    toggleDropdown(btn, content);
  });
});

allMultiSelects.forEach(item => {
    const select = item.querySelector("select");
    const label = item.querySelector(".selected-tags");
    let itemOptions = item.querySelectorAll(".multi-select-option");
    handleOptionsSelect(itemOptions, label, select);
    // updateMultiSelectDisplay(select, label, itemOptions);
});

window.addEventListener("click", function (e) {
    selectToggles.forEach(btn => {
        const content = btn.parentElement.querySelector(".dropdown-content");
        if (!btn.contains(e.target) && content.classList.contains("show")) {
          content.classList.remove("show");
        }
    });
});

// window.addEventListener("keydown", function (event) {
//     if (event.key === "Escape") {
//         if (multiSelectContent.classList.contains("show")) {
//             multiSelectContent.classList.remove("show");
//         }
//     }
// });
