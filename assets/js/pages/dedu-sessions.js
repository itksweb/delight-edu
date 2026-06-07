console.log("dedu-sessions.js loaded");
const sessionTemplate = document.querySelector("#session-template");
const termTemplate = document.querySelector("#term-template");
const sessionDate = document.querySelector("#session-date-template");
const drawer = document.querySelector("#dedu-session-drawer");
const sessionForm = document.querySelector("#dedu-session-form");
const tableBody = document.querySelector(".dedu-table-modern tbody");

// Drawer Internal Target References
const drawerTitle = document.querySelector("#dedu-drawer-title");
const sessionSubmitBtn = document.querySelector("#dedu-drawer-submit-btn");
const sessionIdInput = document.querySelector("#drawer_session_id");
const sessionNameInput = document.querySelector("#drawer_session_name");
const sessionSection = document.querySelector("#drawer-session-section");
const termsContainer = document.querySelector("#term-rows");
const { numeric, ordinal, term_label } = deduSessionData;

// Update the array index in the name attribute: terms[0] -> terms[1]

const populateTermFields = (row, data = {}, i = 0) => {
  const regex = /^terms\[\d+\]/;
  if (!isObjectEmpty(data)) {
    const inputs = row.querySelectorAll("input");
    inputs.forEach((input) => {
      if (regex.test(input.name)) {
        input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
        const key = input.name.split("][")[1].slice(0, -1);
        key === "term_id"
          ? (input.value = data.id ? data.id : "")
          : (input.value = data[key] ? data[key] : "");
      }
    });
  }
};

const addTerm = (data, i) => {
  const newTerm = termTemplate.content.cloneNode(true);
  populateTermFields(newTerm, data, i);
  termsContainer.appendChild(newTerm);
};

const getDefaultTerms = (style = "ordinal") => {
  return deduSessionData[style].map((i) => {
    return { term_name: i, starts: "", ends: "" };
  });
};

const setTermNameStyleToggle = () => {
  const toggle = document.querySelector("#term-name-style-toggle");
  const ordSpan = toggle.previousElementSibling;
  const numSpan = toggle.nextElementSibling;
  const termNames = termsContainer.querySelectorAll(".st-name");
  const isOrdinal = termNames[0].value.endsWith(term_label);
  toggle.dataset.value = isOrdinal ? "numeric" : "ordinal";
  toggle.style.justifyContent = isOrdinal ? "flex-start" : "flex-end";
  if (isOrdinal) {
    ordSpan.classList.add("active");
    numSpan.classList.remove("active")
  } else {
    numSpan.classList.add("active");
    ordSpan.classList.remove("active");
  }
};

const removeSessionDates = () => {
  sessionSection
    .querySelectorAll(".date-space")
    .forEach((input) => input.remove());
};

const disableInput = (input) => {
  input.readOnly = true;
  input.style.pointerEvents = "none";
  input.style.backgroundColor = "transparent";
};
const enableInput = (input) => {
  input.readOnly = false;
  input.style.pointerEvents = "auto";
};

const handleSessionDateChange = (hasTerms = false) => {
  const sessionDates = sessionSection.querySelectorAll(".date-space");
  const termDates = termsContainer.querySelectorAll(".date-space");

  const sessionStart = sessionDates[0];
  const sessionEnd = sessionDates[1];

  const firstTermStarts =
    termsContainer.firstElementChild.querySelector(".start-date");
  const lastTermEnds =
    termsContainer.lastElementChild.querySelector(".end-date");

  const sessionDateMissing = !sessionStart.value || !sessionEnd.value;

  // populate Start Date of 1st term from session start date
  firstTermStarts.type = sessionStart.value ? "date" : "text";
  firstTermStarts.value = sessionStart.value ? sessionStart.value : "";
  sessionStart.value
    ? disableInput(firstTermStarts)
    : enableInput(firstTermStarts);
  limitDate(sessionDates, 0);

  // populate End Date of last term from session end date
  lastTermEnds.type = sessionEnd.value ? "date" : "text";
  lastTermEnds.value = sessionEnd.value ? sessionEnd.value : "";
  sessionEnd.value ? disableInput(lastTermEnds) : enableInput(lastTermEnds);
  limitDate(sessionDates, 1);
  sessionEnd.disabled = !sessionStart.value;

  if (sessionStart.value && sessionEnd.value) {
    firstTermStarts.nextElementSibling.min = firstTermStarts.value;
  }

  // disable and enable the appropriate fields
  termDates.forEach((input, index) => {
    input.min = sessionStart.value;
    input.max = sessionEnd.value;
    if (sessionDateMissing) {
      input.disabled = true;
      input.value = "";
      input.type = "text";
    } else {
      input.disabled = hasTerms
        ? !input.value
        : ![0, 1, termDates.length - 1].includes(index);
    }
  });
};

// --- STATE TRIGGER 1: ADD NEW ENTRY ---
const renderAddNewScreen = () => {
  sessionForm.reset(); // Clear previous inputs cleanly
  sessionIdInput.value = ""; // Crucial: Ensures the system knows it's a creation request
  drawerTitle.textContent = "Add New Academic Session";
  sessionSubmitBtn.textContent = "Initialize Session";
  document.querySelector(".toggle-switch").classList.add("hide-me");

  removeSessionDates(); // Clear session date fields
  termsContainer.replaceChildren(); // Flush rows
  drawer.classList.add("open");
};

// --- STATE TRIGGER 2: EDIT EXISTING ENTRY (AJAX Data Fetch) ---
const renderEditScreen = async (e) => {
  e.preventDefault();
  sessionForm.reset();

  // Grab meta fields out of your row icon button dataset link
  const icon = e.target.closest(".dedu-edit-icon");
  if (!icon) return;

  const { id, name } = icon.dataset;
  drawerTitle.textContent = `Configure Session: ${name}`;
  sessionSubmitBtn.textContent = "Update Configurations";
  sessionIdInput.value = id;
  sessionNameInput.value = name;

  // clear, create and populate session start date and session end date fields dynamically
  removeSessionDates(); // Clear session date fields

  const clone = sessionDate.content.cloneNode(true);
  const startsInput = clone.querySelector("#drawer_starts");
  const endsInput = clone.querySelector("#drawer_ends");

  // Fetch row dates directly from the table layout strings to save API overhead
  const row = icon.closest("tr");
  const tableStarts = row.cells[2].textContent.trim();
  const tableEnds = row.cells[3].textContent.trim();

  startsInput.value = tableStarts ? tableStarts : "";
  endsInput.value = tableEnds ? tableEnds : "";

  if (tableStarts) startsInput.type = "date";
  if (tableEnds) endsInput.type = "date";

  sessionSection.appendChild(startsInput);
  sessionSection.appendChild(endsInput);
  document.querySelector(".toggle-switch").classList.remove("hide-me");

  // Clear old terms and prepare for new term entries population
  termsContainer.replaceChildren();
  drawer.classList.add("open");

  // Fetch dynamic sub-terms via your verified AJAX channel
  const formData = new FormData();
  formData.append("action", "get_session_terms");
  formData.append("id", id);
  formData.append("nonce", deduSessionData.nonce);

  try {
    const response = await fetch(ajaxurl, { method: "POST", body: formData });
    const result = await response.json();

    if (result.success && result.data.terms) {
      console.log("data: ", result.data.terms);
      const hasTerms = result.data.terms.length;
      const terms = hasTerms ? result.data.terms : getDefaultTerms();
      terms.forEach((term, i) => addTerm(term, i));
      setTermNameStyleToggle()

      const termDates = termsContainer.querySelectorAll(".date-space");
      termDates.forEach((input, index) => {
        input.addEventListener("change", () =>
          handleTermDateChange(termDates, index),
        );
      });
      handleSessionDateChange(hasTerms);
    }
  } catch (error) {
    console.error("Failed to populate drawer child entries:", error);
  }
};

// --- FORM SUBMISSION (Decides Create vs Update automatically) ---
sessionForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(sessionForm);
  const isUpdate = sessionIdInput.value !== "";
  // console.log(isUpdate ? "Updating session..." : "Creating new session...");

  // Dynamic routing routing target action allocation
  formData.append(
    "action",
    isUpdate ? "dedu_update_session_async" : "dedu_create_session_async",
  );

  try {
    const response = await fetch(ajaxurl, { method: "POST", body: formData });
    const result = await response.json();

    if (result.success) {
      drawer.classList.remove("open");
      sessionForm.reset();

      if (window.triggerDeduToast) {
        window.triggerDeduToast(
          isUpdate
            ? "Configurations updated!"
            : "Session created successfully!",
        );
      }

      // Reload your modules table view list components silently here or run location.reload()
      // If your table is simple, reloading the view via location.reload() completely ensures all terms badge calculations are perfect!
      setTimeout(() => location.reload(), 1000);
    } else {
      alert("Error: " + result.data);
    }
  } catch (error) {
    console.error("Submission error:", error);
  }
});

document.addEventListener("click", (e) => {
  if (
    e.target.matches("#dedu-close-drawer") ||
    !target(e, "#dedu-session-drawer")
  ) {
    drawer.classList.remove("open");
  }
  if (target(e, ".custom-toggle")) {
    switchTermNameStyle(target(e, ".custom-toggle"));
  }
});

document.addEventListener("change", (e) => {
  if (e.target.matches("#drawer-session-section .date-space")) {
    handleSessionDateChange();
  }
});

document.addEventListener(
  "focus",
  (e) => {
    if (e.target.matches(".date-space")) {
      if (!e.target.matches(".end-date")) {
        e.target.type = "date";
      } else {
        if (e.target.previousElementSibling.value) {
          e.target.type = "date";
        } else {
          e.target.type = "text";
        }
      }
      if (typeof e.target.showPicker === "function") e.target.showPicker();
    }
  },
  true,
);

document.addEventListener(
  "blur",
  (e) => {
    if (e.target.matches(".date-space") && !e.target.value) {
      e.target.type = "text";
    }
  },
  true,
);

const handleTermDateChange = (dateInputs, index) => {
  limitDate(dateInputs, index);
  const input = dateInputs[index];
  const prevInput = dateInputs[index - 1];
  const nextInput = dateInputs[index + 1];

  if (!input.value) {
    input.type = "text";
  } else {
    if (index % 2 === 1 && prevInput && !prevInput.value) {
      // ensure start date is filled before end date
      alert("Enter start date before end date");
      input.value = "";
      input.type = "text";
      prevInput.disabled = false;
      prevInput.focus();
      return;
    }
    nextInput && (nextInput.disabled = !input.value);
  }
};
const limitDate = (dateInputs, index) => {
  const input = dateInputs[index];
  const currentValue = input.value;
  // 1. If value is cleared, you might want to reset boundaries (optional)
  if (!currentValue) return;

  const previousInput = dateInputs[index - 1];
  const nextInput = dateInputs[index + 1];

  // 2. Constrain the NEXT input (it cannot be earlier than this one)
  if (nextInput) nextInput.min = currentValue;

  // 3. Constrain the PREVIOUS input (it cannot be later than this one)
  if (previousInput) previousInput.max = currentValue;
};

const switchTermNameStyle = (toggle) => {
  const options = ["ordinal", "numeric"];
  if (!options.includes(toggle.dataset.value)) return;
  const ordSpan = toggle.previousElementSibling;
  const numSpan = toggle.nextElementSibling;

  const termNames = termsContainer.querySelectorAll(".st-name");
  termNames.forEach((el, i) => {
    el.value = deduSessionData[toggle.dataset.value][i];
  });
  if (toggle.dataset.value === "numeric") {
    toggle.style.justifyContent = "flex-end";
    ordSpan.classList.remove("active");
    numSpan.classList.add("active");
    toggle.dataset.value = "ordinal";
  } else if (toggle.dataset.value === "ordinal") {
    toggle.style.justifyContent = "flex-start";
    numSpan.classList.remove("active");
    ordSpan.classList.add("active");
    toggle.dataset.value = "numeric";
  }
  
};
