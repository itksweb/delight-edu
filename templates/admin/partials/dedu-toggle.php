
<div class="toggle-switch flex justify-center items-center w-full">
    <span class="" data-id="ordinal">ordinal</span>
    <span class="custom-toggle" id="term-name-style-toggle" data-value="numeric">
        <span class="switch-inner"></span>
    </span>
    <span class="" data-id="numeric" >numeric</span>
</div>

<style>
    .toggle-switch {
        display: flex; 
        align-items: center;
        justify-content: center;
        margin-top: 20px;
        .active {
            color: #0a0441;
            font-weight: 600;
        }
    }
    .toggle-switch.hide-me {
        display: none;
    }
    .custom-toggle {
        /* Dimensions & Cursor */
        width: 1.75rem; /* w-7 (28px) */
        height: 0.875rem; /* h-3.5 (14px) */
        cursor: pointer; 

        /* Margins & Padding */
        margin-left: 0.5rem; 
        margin-right: 0.5rem;
        padding: 2px; 

        /* Background & Borders */
        background-color: #0a0441; 
        border-radius: 9999px; 

        /* Layout */
        display: flex; 
        align-items: center;
        justify-content: flex-start;

        .switch-inner {
            background-color: #ffffff; 
            border-radius: 9999px; 

            /* Dimensions (Mobile/Base) */
            width: 0.625rem; 
            height: 0.625rem; 
        }
    }
</style>