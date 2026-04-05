console.log("Dedu JS Active");

const toast = document.querySelector("#dedu-toast");
if (toast) showNotification(toast);

/*=========================================
        ==>  SELECTION CHECKBOXES  
==========================================*/
const tableBody = document.querySelector(".dedu-table-modern");
const selectAllCheckbox = document.querySelector("#dedu-select-all");
const all = document.querySelectorAll(".dedu-selection-checkbox");

// --- 1. SELECT ALL CHECKBOXES ---
checkUncheckAll("#dedu-select-all", ".dedu-selection-checkbox", tableBody);  

// --- 2. INDIVIDUAL CHECKBOX LOGIC ---
checkUncheckSingle(tableBody, "#dedu-select-all", "dedu-selection-checkbox");  


/*=======================================
        <==  SELECTION CHECKBOXES  
=========================================*/


/*=========================================
        ==>      BULK ACTION  
==========================================*/
const applyBulkBtn = document.querySelector("#dedu-apply-bulk-action");
bulkAction(
  applyBulkBtn,
  "#dedu-bulk-action-selector",
  ".dedu-selection-checkbox:checked"
);
/*=======================================
        <==      BULK ACTION  
========================================= 



==================================================
==>  LIST - FORM TOGGLE & SINGLE DELETE  
=================================================*/
document.addEventListener("click", (e) => {
  if (target(e, "#show-list-btn")) showListView();
  else if (target(e, "#show-form-btn")) renderAddNewScreen();
  else if (target(e, ".dedu-edit-icon")) renderEditScreen(e);
  else if (target(e, ".dedu-delete-icon")) deleteOne(e)
});
/*=================================================
<==  LIST - FORM TOGGLE & SINGLE DELETE  
===================================================*/

paginateTable();

