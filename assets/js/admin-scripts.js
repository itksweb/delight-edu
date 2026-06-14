console.log("Dedu JS Active");
const listForm = ["settings", "attendance"];

/*=========================================
        ==>  TOAST NOTIFICATION  
==========================================*/


const showNotification = (toast) => {
  setTimeout(() => toast.classList.add("show"), 100); // Delay slightly for smooth entrance
  setTimeout(() => toast.classList.remove("show"), 4000); // Auto-hide after 4 seconds

  // Optional: Clean the URL so the toast doesn't pop up again on refresh
  if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete("message");
    url.searchParams.delete("error");
    url.searchParams.delete("count");
    url.searchParams.delete("settings-updated");
    window.history.replaceState({}, "", url);
  }
};
const toast = document.querySelector("#dedu-toast");
if (toast) showNotification(toast);

/*=========================================
        <==  TOAST NOTIFICATION  
==========================================*/

const itemTypSlug = document.querySelector(".wrap").dataset.type;

if (!listForm.includes(itemTypSlug)) {
  document.addEventListener("click", (e) => {
    if (target(e, "#show-list-btn")) showListView();
    else if (target(e, "#show-form-btn")) renderAddNewScreen();
    else if (target(e, ".dedu-edit-icon")) renderEditScreen(e);
    else if (target(e, ".dedu-delete-icon")) deleteOne(e);
  });

  const applyBulkBtn = document.querySelector("#dedu-apply-bulk-action");
  bulkAction(
    applyBulkBtn,
    "#dedu-bulk-action-selector",
    ".dedu-selection-checkbox:checked",
  );

  const tableBody = document.querySelector(".dedu-table-modern");
  const selectAllCheckbox = document.querySelector("#dedu-select-all");
  const all = document.querySelectorAll(".dedu-selection-checkbox");

  // --- 1. SELECT ALL CHECKBOXES ---
  checkUncheckAll("#dedu-select-all", ".dedu-selection-checkbox", tableBody);

  // --- 2. INDIVIDUAL CHECKBOX LOGIC ---
  checkUncheckSingle(tableBody, "#dedu-select-all", "dedu-selection-checkbox");

  paginateTable();
}


