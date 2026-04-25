<div class="unit dedu-upload-container bordered" id="drop-zone">
    <label for="staff_photo" class="dedu-upload-label">
        <div class="upload-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
        </div>
        <p class="upload-text" >
            <strong>Click to upload</strong> or drag and drop  
            <span>PNG, JPG or GIF (max. 2MB)</span>
        </p>
        <input type="file" name="staff_photo" id="staff_photo" accept="image/*" hidden>
    </label>
    
    <div id="image-preview" class="image-preview hidden">
        <img src="" alt="Preview">
        <button type="button" class="remove-img">&times;</button>
    </div>
</div>

<style>
    .dedu-upload-container {
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: all 0.3s ease;
    background: #f9fafb;
    cursor: pointer;
    grid-row: span 3;
    }

    .bordered {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 40px;
    }

    .dedu-upload-container:hover, 
    .dedu-upload-container.drag-over {
        border-color: #4f46e5; /* Modern Indigo */
        background: #f5f3ff;
    }

    .upload-icon { 
        color: #9ca3af; 
        margin-bottom: 12px; 
        margin: 0 auto;
        display: flex; 
        align-items:center;
        justify-content: center;
    }
    .upload-text { 
        color: #4b5563; 
        display: flex; 
        flex-direction: column; 
        align-items:center
    }
    .upload-text strong { color: #4f46e5; }
    .upload-text span { display: block; color: #9ca3af; margin-top: 4px; }

    /* Preview Styling */
    .image-preview {
        position: relative;
        width: 200px;
        height: 200px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview img {
        /* max-height: 100%; */
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .hidden { display: none !important; }

    .remove-img {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
    }
</style>