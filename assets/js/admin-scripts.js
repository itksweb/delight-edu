jQuery(document).ready(function ($) {
  console.log("Dedu JS Active");

  /*=========================================
          ==>  TOAST NOTIFICATION  
  ==========================================*/
  const $toast = $("#dedu-toast");
  if ($toast.length) {
    // Delay slightly for smooth entrance
    setTimeout(() => $toast.addClass("show"), 300);

    // Auto-hide after 4.5 seconds
    setTimeout(() => $toast.removeClass("show"), 4500);

    // Optional: Clean the URL so the toast doesn't pop up again on refresh
    if (window.history.replaceState) {
      const url = new URL(window.location);
      url.searchParams.delete("message");
      url.searchParams.delete("error");
      url.searchParams.delete("count");
      window.history.replaceState({}, "", url);
    }
  }
  /*=========================================
          <==  TOAST NOTIFICATION  
  ==========================================*/

  const itemTypeSlug = $(".wrap").attr("data-type");
  const itemType = itemTypeSlug
    .split("_")
    .map((str) => str.slice(0, 1).toUpperCase() + str.slice(1))
    .join(" ");

  // --- PAGINATION VARIABLES ---
  let rowsPerPage = parseInt($("#dedu-rows-per-page").val()) || 10;
  // Check URL for existing page number on load
  const urlParams = new URLSearchParams(window.location.search);
  let currentPage = urlParams.has("paged")
    ? parseInt(urlParams.get("paged"))
    : 1;

  // Helper to update URL query string without reloading
  function updateURL(page) {
    const url = new URL(window.location);
    if (urlParams.has("paged")) {
    }
    url.searchParams.set("paged", page);
    window.history.pushState({}, "", url);
  }

  /*=========================================
          ==>  SELECTION CHECKBOXES  
  ==========================================*/

  // --- 1. SELECT ALL CHECKBOXES ---
  $("#dedu-select-all").on("change", function () {
    $(".dedu-selection-checkbox:visible").prop(
      "checked",
      $(this).prop("checked"),
    );
  });

  // --- 2. INDIVIDUAL CHECKBOX LOGIC ---
  $(document).on("change", ".dedu-selection-checkbox", function () {
    const $visibleCheckboxes = $(".dedu-selection-checkbox:visible");
    if (
      $visibleCheckboxes.filter(":checked").length ===
        $visibleCheckboxes.length &&
      $visibleCheckboxes.length > 0
    ) {
      $("#dedu-select-all").prop("checked", true);
    } else {
      $("#dedu-select-all").prop("checked", false);
    }
  });
  /*=======================================
          <==  SELECTION CHECKBOXES  
  =========================================
  
  

  =========================================
          ==>      BULK ACTION  
  ==========================================*/
  $("#dedu-apply-bulk-action").on("click", function () {
    const action = $("#dedu-bulk-action-selector").val();
    const selectedIds = $(".dedu-selection-checkbox:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    if (selectedIds.length === 0 || !action) {
      alert("Please select both an action and at least one role.");
      return;
    }

    if (
      action === "delete" &&
      !confirm(`Are you sure you want to delete ${selectedIds.length} roles?`)
    )
      return;

    const $form = $("<form>", {
      action: ajaxurl.replace("admin-ajax.php", "admin-post.php"),
      method: "POST",
    })
      .append(
        $("<input>", {
          type: "hidden",
          name: "action",
          value: "dedu_bulk_action_roles",
        }),
      )
      .append(
        $("<input>", { type: "hidden", name: "bulk_action", value: action }),
      )
      .append(
        $("<input>", {
          type: "hidden",
          name: "role_ids",
          value: selectedIds.join(","),
        }),
      )
      .append(
        $("<input>", {
          type: "hidden",
          name: "dedu-role-nonce",
          value: $("#dedu-role-nonce").val(),
        }),
      );

    $("body").append($form);
    $form.submit();
  });
  /*=======================================
          <==      BULK ACTION  
  ========================================= 


  =========================================
          ==>      PAGINATION  
  ==========================================*/
  function paginateTable() {
    const $rows = $(".dedu-table-modern tbody tr").not(
      "#dedu-no-search-results, .dedu-no-data-static",
    );

    const $filteredRows = $rows.filter(function () {
      return $(this).data("search-match") !== false;
    });

    const totalRows = $filteredRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    // Safety: If current page exceeds new total pages (after search/resize)
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    $rows.hide();
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    $filteredRows.slice(start, end).show();

    // Update URL and UI
    updateURL(currentPage);
    $("#total-visible-items").text(totalRows);
    $("#current-visible-range").text(
      totalRows > 0 ? `${start + 1}-${Math.min(end, totalRows)}` : "0-0",
    );

    renderPageNumbers(totalPages);
    $("#prev-page").prop("disabled", currentPage === 1 || totalRows === 0);
    $("#next-page").prop(
      "disabled",
      currentPage === totalPages || totalRows === 0 || totalPages === 1,
    );

    updateControlsState();
  }

  function renderPageNumbers(totalPages) {
    let html = "";
    if (totalPages > 1) {
      for (let i = 1; i <= totalPages; i++) {
        html += `<button type="button" class="page-num ${
          i === currentPage ? "active" : ""
        }" data-page="${i}">${i}</button>`;
      }
    }
    $("#page-numbers").html(html);
  }

  // Rows Per Page Change
  $("#dedu-rows-per-page").on("change", function () {
    rowsPerPage = parseInt($(this).val());
    currentPage = 1;
    paginateTable();
  });

  $(document).on("click", ".page-num", function () {
    currentPage = parseInt($(this).data("page"));
    paginateTable();
  });

  $("#prev-page").on("click", function () {
    if (currentPage > 1) {
      currentPage--;
      paginateTable();
    }
  });

  $("#next-page").on("click", function () {
    currentPage++;
    paginateTable();
  });
  /*========================================
          <==      PAGINATION  
  ==========================================
  
  
  
  ==========================================
       ==>  CONTROL STATE & SEARCH  
  ==========================================*/

  function updateControlsState() {
    const isDbEmpty = $(".dedu-no-data-static").is(":visible");
    const $rows = $(".dedu-table-modern tbody tr").not(
      "#dedu-no-search-results, .dedu-no-data-static",
    );
    const hasMatches =
      $rows.filter(function () {
        return $(this).data("search-match") !== false;
      }).length > 0;
    const shouldDisable = isDbEmpty || !hasMatches;

    $("#dedu-select-all").prop("disabled", shouldDisable);
    $("#dedu-bulk-action-selector").prop("disabled", shouldDisable);
    $("#dedu-apply-bulk-action").prop("disabled", shouldDisable);

    if (shouldDisable) $("#dedu-select-all").prop("checked", false);
  }

  $("#dedu-search").on("keyup", function () {
    const value = $(this).val().toLowerCase().trim();
    let matchCount = 0;
    const $rows = $(".dedu-table-modern tbody tr").not(
      "#dedu-no-search-results, .dedu-no-data-static",
    );

    $rows.each(function () {
      const roleName = $(this).find("td").eq(1).text().toLowerCase().trim();
      const match = roleName.startsWith(value);
      $(this).data("search-match", match);
      if (match) matchCount++;
    });

    currentPage = 1;
    $("#dedu-no-search-results").toggle(matchCount === 0 && value !== "");
    paginateTable();
  });
  /*=========================================
       <==  CONTROL STATE & SEARCH  
  ===========================================
  


  ==========================================
          ==>  LIST - FORM TOGGLE  
  ==========================================*/

  //-- Helper Functions --//
  const $form = $("#dedu-form-view form");
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

  const updateHiddenInput = (id = null) => {
    if (itemTypeSlug !== "class_subjects") {
      if (id) {
        if ($(`input[name=${itemTypeSlug}_id]`).length === 0) {
          $form.prepend(
            `<input type="hidden" name=${itemTypeSlug}_id value="${id}">`,
          );
        } else {
          $(`input[name=${itemTypeSlug}_id]`).val(id);
        }
      } else {
        $(`input[name=${itemTypeSlug}_id]`).remove(); // Remove hidden ID if it exists
      }
    }
  };

  const changeText = (title, btnText) => {
    const $submitBtn = $form.find('button[type="submit"]');
    const $headerTitle = $("#dedu-form-view h3");
    $headerTitle.text(title);
    $submitBtn.text(btnText);
  };

  const showFormView = () => {
    $("#dedu-list-view").hide();
    $("#dedu-form-view").fadeIn(300);
  };

  const showListView = () => {
    $("#dedu-form-view").hide();
    $("#dedu-list-view").fadeIn(300);
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

  $(document).on("click", "#show-form-btn", function () {
    if (itemTypeSlug !== "staff") {
      changeText(`Add New ${itemType}`, `Create ${itemType}`);
    }

    renderAddNewScreen();
    showFormView();
    updateUrlActionId();
    updateHiddenInput();
  });

  $(document).on("click", "#show-list-btn", () => showListView());

  $(document).on("click", ".dedu-edit-icon", function (e) {
    e.preventDefault();
    const data = { ...this.dataset };
    if (itemTypeSlug === "staff") {
      const staffId = data.id;
      const $btn = $(this);
    }
    changeText(`Edit ${itemType}: ${data.name}`, `Update ${itemType}`);
    showFormView();
    renderEditScreen(data);

    updateUrlActionId(data.id);
    updateHiddenInput(data.id);
  });
  /*=========================================
         <==  LIST - FORM TOGGLE  
  ==========================================


  =========================================
          ==>  SINGLE DELETE  
  =========================================*/

  $(document).on("click", ".dedu-delete-icon", function (e) {
    e.preventDefault();
    const data = this.dataset;
    const name =
      itemTypeSlug === "staff" ? `${data.fname} ${data.lname}` : data.name;
    const { id, nonce } = data;

    if (confirm(`Are you sure you want to delete the "${name}" ${itemType}?`)) {
      const baseAdminUrl = ajaxurl.replace("admin-ajax.php", "admin-post.php");
      window.location.href = `${baseAdminUrl}?action=dedu_delete_${itemTypeSlug}&id=${id}&_wpnonce=${nonce}`;
    }
  });
  /*=========================================
          <==  SINGLE DELETE  
  ==========================================*/

  // AJAX Form Submission
  // $("#dedu-form-view form").on("submit", function (e) {
  //   e.preventDefault();

  //   const $form = $(this);
  //   const $submitBtn = $form.find('button[type="submit"]');
  //   const originalBtnText = $submitBtn.text();

  //   // UI Feedback: Loading
  //   $submitBtn.text("Saving...").prop("disabled", true);

  //   $.ajax({
  //     url: ajaxurl, // Global WP JS variable
  //     type: "POST",
  //     data: $form.serialize() + "&action=dedu_save_class_ajax",
  //     success: function (response) {
  //       if (response.success) {
  //         // 1. Update the table body with new HTML
  //         $(".dedu-table-modern tbody").html(response.data.html);

  //         // 2. Reset the form
  //         $form[0].reset();
  //         $("#sections-container").html(
  //           '<div class="dedu-form-group section-row"><input type="text" name="sections[]" placeholder="Section Name (e.g. A)" required><input type="text" name="cate" placeholder="e.g. Law, Sciences, etc."><button type="button" class="remove-section">×</button></div>',
  //         );

  //         // 3. Switch back to list view
  //         $formView.hide();
  //         $listView.fadeIn();

  //         // Optional: You could trigger a toast notification here
  //       } else {
  //         alert("Error: " + response.data.message);
  //       }
  //     },
  //     error: function () {
  //       alert("An unexpected error occurred.");
  //     },
  //     complete: function () {
  //       $submitBtn.text(originalBtnText).prop("disabled", false);
  //     },
  //   });
  // });

  paginateTable();
});
