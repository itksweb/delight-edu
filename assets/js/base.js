console.log("base JS loaded");
const itemTypeSlug = document.querySelector(".wrap").dataset.type;
const itemType = itemTypeSlug
  .split("_")
  .map((str) => str.slice(0, 1).toUpperCase() + str.slice(1))
  .join(" ");

const formView = document.querySelector("#dedu-form-view");
const listView = document.querySelector("#dedu-list-view");
const formTitle = formView.querySelector("h3");
const listTitle = listView.querySelector("h3");
const form = formView.querySelector("form");
const submitBtn = form.querySelector('button[type="submit"]');



/*=========================================
        ==>  TOAST NOTIFICATION  
==========================================*/
const showNotification = (toast) => {
  setTimeout(() => toast.classList.add("show"), 200); // Delay slightly for smooth entrance
  setTimeout(() => toast.classList.remove("show"), 4000); // Auto-hide after 4 seconds

  // Optional: Clean the URL so the toast doesn't pop up again on refresh
  if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete("message");
    url.searchParams.delete("error");
    url.searchParams.delete("count");
    window.history.replaceState({}, "", url);
  }
};
/*=========================================
        <==  TOAST NOTIFICATION  
==========================================*/



/*=========================================
        ==>      PAGINATION  
==========================================*/
let rowsPerPage = document.querySelector("#dedu-rows-per-page").value || 10;
const urlParams = new URLSearchParams(window.location.search); // Check URL for existing page number on load
let currentPage = urlParams.has("paged") ? +urlParams.get("paged") : 1;

const pageRows = document.querySelector("#dedu-rows-per-page");
const pageNumbers = document.querySelector("#page-numbers");
const prevPage = document.querySelector("#prev-page");
const nextPage = document.querySelector("#next-page");

const target = (e, selector) => e.target.closest(selector);
const changeText = (title, btnText) => {
  formTitle.textContent = title;
  submitBtn.textContent = btnText;
};
const creatIt = (tag = "span", cls = "", text = "") => {
  const element = document.createElement(tag);
  if (cls) element.classList.add(cls);
  if (text) element.textContent = text;
  return element;
};

const updateHiddenInput = (id = null) => {
  if (itemTypeSlug !== "class_subjects") {
    // 1. Create the selector string for the hidden input
    const inputName = `${itemTypeSlug}_id`;
    const existingInput = document.querySelector(`input[name="${inputName}"]`);

    if (id) {
      if (!existingInput) {
        // 2. If it doesn't exist, prepend it to the form
        // 'afterbegin' is the native equivalent to jQuery's .prepend()
        const inputHtml = `<input type="hidden" name="${inputName}" value="${id}">`;
        form.insertAdjacentHTML("afterbegin", inputHtml);
      } else {
        // 3. If it exists, just update the value
        existingInput.value = id;
      }
    } else {
      // 4. If id is null, remove the input if it exists
      existingInput?.remove();
    }
  }
};

const updateUrlActionId = (id = null) => {
  let url = new URL(window.location);
  url.searchParams.delete("paged");
  if (itemTypeSlug !== "class_subjects") {
    url.searchParams.set("action", id ? "edit" : "add");
    if (id) url.searchParams.set("id", id);
  } else {
    if (id) url.searchParams.set("class_id", id);
  }

  window.history.pushState({}, "", url);
};

function renderPageNumbers(totalPages) {
  let html = "";
  if (totalPages > 1) {
    for (let i = 1; i <= totalPages; i++) {
      html += `<button type="button" class="page-num ${
        i === currentPage ? "active" : ""
      }" data-page="${i}">${i}</button>`;
    }
  }
  pageNumbers.innerHTML = html;
  // $("#page-numbers").html(html);
}

function updateURL(page) {
  const url = new URL(window.location);
  if (urlParams.has("paged")) {
  }
  url.searchParams.set("paged", page);
  window.history.pushState({}, "", url);
}

const updateControlsState = (rows) => {
  const dbEmpty = document.querySelector(".dedu-no-data-static");
  const selectors = [
    "#dedu-select-all",
    "#dedu-bulk-action-selector",
    "#dedu-apply-bulk-action",
  ];
  const shouldDisable = dbEmpty || !rows.length;
  selectors.forEach(
    (selector) => (document.querySelector(selector).disabled = shouldDisable)
  );
  if (shouldDisable) document.querySelector(selectors[0]).checked = false;
};

const paginateTable = (trs = {}) => {
  const aRows = document.querySelectorAll(".dedu-table-modern .is-row");
  const rows = trs.hasOwnProperty("trs") ? trs.trs : [...aRows];
  const totalVisible = document.querySelector("#total-visible-items");
  const currentVisible = document.querySelector("#current-visible-range");

  const totalRows = rows.length;
  const totalPages = Math.ceil(totalRows / rowsPerPage);

  // Safety: If current page exceeds new total pages (after search/resize)
  if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  aRows.forEach((row) => (row.style.display = "none"));
  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.slice(start, end).forEach((row) => (row.style.display = ""));

  updateURL(currentPage);
  totalVisible.textContent = totalRows;
  currentVisible.textContent =
    totalRows > 0 ? `${start + 1}-${Math.min(end, totalRows)}` : "0-0";

  renderPageNumbers(totalPages);
  prevPage.disabled = currentPage === 1 || totalRows === 0;
  nextPage.disabled =
    currentPage === totalPages || totalRows === 0 || totalPages === 1;

  updateControlsState(rows);
};

// Rows Per Page Change
pageRows.addEventListener("change", (e) => {
  rowsPerPage = +e.target.value;
  currentPage = 1;
  paginateTable();
});

//  Go to specific page
pageNumbers.addEventListener("click", (e) => {
  if (e.target.classList.contains("page-num")) {
    currentPage = +e.target.dataset.page;
    paginateTable();
  }
});

// Go to previous page
prevPage.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    paginateTable();
  }
});

//  Go to next page
nextPage.addEventListener("click", () => {
  currentPage++;
  paginateTable();
});

/*========================================
        <==      PAGINATION  
==========================================



==========================================
      ==>  CONTROL STATE & SEARCH  
==========================================*/
const searchBox = document.querySelector("#dedu-search");
searchBox.addEventListener("keyup", (e) => {
  const value = e.target.value.toLowerCase().trim();
  const noSearchResult = document.querySelector("#dedu-no-search-results");
  const rows = document.querySelectorAll(".dedu-table-modern .is-row");
  const filteredRows = [...rows].filter((row) => {
    let rowName = row
      .querySelector(".text-heading")
      .textContent.toLowerCase()
      .trim();
    return rowName.startsWith(value);
  });
  const newRows = value ? filteredRows : [...rows];
  currentPage = 1;
  if (value && filteredRows.length === 0) noSearchResult.style.display = "";
  paginateTable({ trs: newRows });
});
/*=========================================
      <==  CONTROL STATE & SEARCH  
===========================================*/


/*=========================================
        ==>  CUSTOM MULTIPLE SELECT  
==========================================*/
const customiseMultiselect = (tr = null) => {
  const base = tr ? tr : document;

  const updateMultiSelectDisplay = (content) => {
    const select = content.closest(".multi-select").querySelector("select");
    const label = content
      .closest(".multi-select")
      .querySelector(".selected-tags");
    const options = content.querySelectorAll(".multi-select-option");

    const values = [...select.selectedOptions].map((opt) => opt.value);
    label.innerHTML = "";
    if (values.length) {
      values.forEach((value) => {
        const optMatch = [...options].find(
          (opt) => opt.dataset.value === value
        );
        if (optMatch) {
          const tag = creatIt("span", "selected-tag", optMatch.textContent);
          label.appendChild(tag);
        }
      });
    } else {
      const placeholder = creatIt("span", "placeholder", "choose");
      label.appendChild(placeholder);
    }
  };

  const handleSelection = (content) => {
    const select = content.closest(".multi-select").querySelector("select");
    content.addEventListener("click", (e) => {
      const option = e.target.closest('[role="option"]');
      if (!option) return;
      const realOption = [...select.options].find(
        (o) => o.value === option.dataset.value
      );
      realOption.selected = !realOption.selected;
      option.setAttribute("aria-selected", realOption.selected);
      option.classList.contains("selected")
        ? option.classList.remove("selected")
        : option.classList.add("selected");

      //Update selected items display
      updateMultiSelectDisplay(content);
    });
  };

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
    const tooggleBtn = creatIt("button", "multi-select-button");
    tooggleBtn.setAttribute("type", "button");
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
    tooggleBtn.innerHTML = btnInner;
    tooggleBtn.setAttribute("aria-haspopup", "listbox");
    tooggleBtn.setAttribute("aria-expanded", "false");
    return tooggleBtn;
  };

  //Build custom dropdown options
  allMultiSelects.forEach((item) => {
    const select = item.querySelector("select");
    const btn = toggleBtn();
    const ul = creatIt("ul", "dropdown-content");
    ul.setAttribute("role", "listbox");
    ul.setAttribute("hidden", "true");
    ul.setAttribute("aria-multiselectable", "true");
    const options = [...select.options]
      .map((opt) => {
        const selected = opt.selected ? "selected" : "";
        return `<li role = "option" aria-selected = ${opt.selected} data-value = ${opt.value} class = "multi-select-option ${selected}"><span>${opt.text}</span><span class = "checkbox-icon" >${checkOption}</span></li>`;
      })
      .join("");
    ul.innerHTML = options;
    select.hidden = true;
    item.appendChild(btn);
    item.appendChild(ul);
  });

  const selectToggles = base.querySelectorAll(".multi-select-button");

  //handling dropdown toggle
  selectToggles.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", !expanded);
      const content = btn.parentElement.querySelector("ul");
      console.log(content);
      content.hidden = expanded;
    });
  });

  const allDropdownContent = base.querySelectorAll(".dropdown-content");

  //handle selection
  allDropdownContent.forEach((content) => {
    handleSelection(content);
    updateMultiSelectDisplay(content); //Update db-selected items display on pageload
  });

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
  return tr;
};
/*=========================================
        <==  CUSTOM MULTIPLE SELECT  
==========================================*/



const showFormView = () => {
  listView.classList.add("hide-me");
  formView.classList.remove("hide-me");
};

const showListView = () => {
  formView.classList.add("hide-me");
  listView.classList.remove("hide-me");
  let url = new URL(window.location);
  const urlParams = new URLSearchParams(window.location.search);
  const checking = ["action", "id", "class_id"];
  for (const item of checking) {
    if (urlParams.has(item)) {
      urlParams.delete(item);
    }
  }
  const newSearch = urlParams.toString();
  const newPath = url.pathname + (newSearch ? `?${newSearch}` : "");
  window.history.replaceState({}, document.title, newPath);
  paginateTable();
};
