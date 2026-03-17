const creatIt = (tag = "span", cls = "", text = "") => {
  const element = document.createElement(tag);
  if (cls) element.classList.add(cls);
  if (text) element.textContent = text;
  return element;
};

const customiseMultiselect = (tr = null) => {
    const base = tr ? tr: document;

  const updateMultiSelectDisplay = (content) => {
    const select = content.closest(".multi-select").querySelector("select")
    const label = content.closest(".multi-select").querySelector(".selected-tags")
    const options = content.querySelectorAll(".multi-select-option")

    const values = [...select.selectedOptions].map((opt) => opt.value);
    label.innerHTML = "";
    // console.log("label: ",label)
    if (values.length) {
        values.forEach(value => {
            const optMatch = [...options].find((opt) => opt.dataset.value === value);
            if (optMatch) {
                const tag = creatIt("span", "selected-tag", optMatch.textContent);
                label.appendChild(tag);
            }
        })
    } else {
        const placeholder = creatIt("span", "placeholder", "choose");
        label.appendChild(placeholder);
    }
  }

  const handleSelection = (content) => {
    const select = content.closest(".multi-select").querySelector("select")

    content.addEventListener("click", (e) => {
        const option = e.target.closest('[role="option"]');
        if (!option) return;
        const realOption = [...select.options].find(o => o.value === option.dataset.value);
        realOption.selected = !realOption.selected;
        option.setAttribute("aria-selected", realOption.selected);
        option.classList.contains("selected")
            ? option.classList.remove("selected")
            : option.classList.add("selected");

        //Update selected items display
        updateMultiSelectDisplay(content)
    
    })
  }
  
 
  const allMultiSelects = base.querySelectorAll(".multi-select");
  const checkOption = `<svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="feather feather-check"
                >
                    <polyline points="20 6 9 17 4 12"></polyline>
  </svg>`;

  const toggleBtn = () => {
    const toggleBtn = creatIt("button", "multi-select-button");
    toggleBtn.setAttribute("type", "button");
    const btnInner = `<span class="selected-tags">
            <span class="placeholder">Choose</span>
        </span>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
            />
      </svg>`;
    toggleBtn.innerHTML = btnInner;
    toggleBtn.setAttribute("aria-haspopup", "listbox");
    toggleBtn.setAttribute("aria-expanded", "false");
    return toggleBtn;
  };

  //Build custom dropdown options
  allMultiSelects.forEach((item) => {
    const select = item.querySelector("select");
    const btn = toggleBtn();
    const ul = creatIt("ul", "dropdown-content");
    ul.setAttribute("role", "listbox");
    ul.setAttribute("hidden", "true");
    ul.setAttribute("aria-multiselectable", "true")
    const options = [...select.options]
      .map((opt) => {
        const selected = opt.selected ? "selected" : "";
        return `<li role = "option" aria-selected = ${opt.selected} data-value = ${opt.value} class = "multi-select-option ${selected}"><span>${opt.text}</span><span class = "checkbox-icon" >${checkOption}</span></li>`;
      })
      .join("");
      ul.innerHTML = options
    select.hidden = true;
    item.appendChild(btn);
    item.appendChild(ul);
  });

  const selectToggles = base.querySelectorAll(".multi-select-button");

  //handling dropdown toggle
  selectToggles.forEach(btn => {
    btn.addEventListener("click", (e) =>{
        const expanded = btn.getAttribute("aria-expanded") === "true";
        btn.setAttribute("aria-expanded", !expanded);
        const content = btn.parentElement.querySelector("ul")
        content.hidden = expanded;
    })
  })


  const allDropdownContent = base.querySelectorAll(".dropdown-content");

  //handle selection
  allDropdownContent.forEach(content => handleSelection(content))

  //close dropdown on click outside
  window.addEventListener("click", function (e) {
    selectToggles.forEach((btn) => {
      const content = btn.parentElement.querySelector(".dropdown-content");

      if (!btn.contains(e.target) && content.hidden == false) {
        content.hidden = true;
        btn.setAttribute("aria-expanded", false);
      }
    });
  });
//    return tr;
};

customiseMultiselect()