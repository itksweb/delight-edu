function buildOptions(select, dropdown) {
    dropdown.innerHTML = "";

    [...select.options].forEach((opt, index) => {
        const li = document.createElement("li");

        li.textContent = opt.text;
        li.dataset.value = opt.value;

        li.setAttribute("role", "option");
        li.setAttribute("aria-selected", opt.selected);

        dropdown.appendChild(li);
    });
}

const select = document.querySelector("#skills");
const dropdown = document.querySelector(".select-dropdown");

buildOptions(select, dropdown);

dropdown.addEventListener("click", e => {
    const option = e.target.closest('[role="option"]');
    if (!option) return;

    const value = option.dataset.value;
    console.log(value)
    const realOption = [...select.options].find(o => o.value === value);
    realOption.selected = !realOption.selected;
    option.setAttribute("aria-selected", realOption.selected);
    updateLabel(select, toggle);
});

const toggle = document.querySelector(".select-toggle");

toggle.addEventListener("click", () => {
    const expanded = toggle.getAttribute("aria-expanded") === "true";

    toggle.setAttribute("aria-expanded", !expanded);

    dropdown.hidden = expanded;
});

function updateLabel(select, button) {

  const selected = [...select.selectedOptions]
        .map(o => o.text);

  button.textContent =
      selected.length
      ? selected.join(", ")
      : "Select skills";

}


