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
if (selectAllCheckbox) {
  selectAllCheckbox.addEventListener("change", (e) => {
    const isChecked = e.target.checked; //
    [...all].forEach((checkbox) => {
      if (checkbox.checkVisibility()) checkbox.checked = isChecked;
    });
  });
}
// --- 2. INDIVIDUAL CHECKBOX LOGIC ---
if (tableBody && selectAllCheckbox) {
  tableBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("dedu-selection-checkbox")) {
      //  filter out only the visible checkboxes
      const visibleCheckboxes = [...all].filter((cb) => cb.checkVisibility());

      //  Count how many of those visible boxes are checked
      const checkedVisibleCount = visibleCheckboxes.filter(
        (cb) => cb.checked
      ).length;

      //  Update the master "Select All" state
      const allChecked =
        visibleCheckboxes.length > 0 &&
        checkedVisibleCount === visibleCheckboxes.length;

      selectAllCheckbox.checked = allChecked;
    }
  });
}
/*=======================================
        <==  SELECTION CHECKBOXES  
=========================================


=========================================
        ==>      BULK ACTION  
==========================================*/
const applyBulkBtn = document.querySelector("#dedu-apply-bulk-action");
applyBulkBtn?.addEventListener("click", () => {
  // 1. Get the action value
  const action = document.querySelector("#dedu-bulk-action-selector")?.value;

  // 2. Get selected IDs (replaces .map().get())
  const selectedCheckboxes = document.querySelectorAll(
    ".dedu-selection-checkbox:checked"
  );
  const selectedIds = [...selectedCheckboxes].map((cb) => cb.value);

  // 3. Validation
  if (selectedIds.length === 0 || !action) {
    alert(`Please select both an action and at least one ${itemType}.`);
    return;
  }

  // 4. Confirmation
  if (
    action === "delete" &&
    !confirm(
      `Are you sure you want to delete ${selectedIds.length} ${itemType}s?`
    )
  ) {
    return;
  }

  // 5. Create the form (replaces $("<form>"))
  const form = document.createElement("form");
  form.action = ajaxurl.replace("admin-ajax.php", "admin-post.php");
  form.method = "POST";
  form.style.display = "none";

  // Helper function to add hidden inputs
  const addInput = (name, value) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    form.appendChild(input);
  };

  // 6. Append data to form
  addInput("action", `dedu_bulk_action_${itemTypeSlug}s`);
  addInput("bulk_action", action);
  addInput(`${itemTypeSlug}s_ids`, selectedIds.join(","));
  addInput(
    `dedu-${itemTypeSlug}-nonce`,
    document.querySelector("#dedu-role-nonce")?.value || ""
  );

  // 7. Submit
  document.body.appendChild(form);
  form.submit();
});
/*=======================================
        <==      BULK ACTION  
========================================= 


==================================================
==>  LIST - FORM TOGGLE & SINGLE DELETE  
=================================================*/

document.addEventListener("click", (e) => {
  if (target(e, "#show-list-btn")) showListView();
  else if (target(e, "#show-form-btn")) {
    renderAddNewScreen();
    showFormView();
    updateUrlActionId();
    updateHiddenInput();
  } else if (target(e, ".dedu-edit-icon")) {
    const data = target(e, ".dedu-edit-icon").dataset;
    renderEditScreen(data);
    showFormView();
    updateUrlActionId(data.id);
    updateHiddenInput(data.id);
  } else if (target(e, ".dedu-delete-icon")) {
    e.preventDefault();
    const data = target(e, ".dedu-delete-icon").dataset;
    const name = ["staff", "student"].includes(itemTypeSlug)
      ? `${data.fname} ${data.lname}`
      : data.name;
    const { id, nonce } = data;
    if (confirm(`Are you sure you want to delete the "${name}" ${itemType}?`)) {
      const baseAdminUrl = ajaxurl.replace("admin-ajax.php", "admin-post.php");
      window.location.href = `${baseAdminUrl}?action=dedu_delete_${itemTypeSlug}&id=${id}&_wpnonce=${nonce}`;
    }
  }
});

/*=================================================
<==  LIST - FORM TOGGLE & SINGLE DELETE  
===================================================*/

paginateTable();

