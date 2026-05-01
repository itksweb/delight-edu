<?php 
    $cls = $sub_pix ? "dedu-upload-container image-upload {$sub_pix}" : "dedu-upload-container image-upload";
?>

<div class="<?php echo esc_attr($cls) ?>">
    <label for="<?php echo esc_attr($field_name) ?>" class="dedu-upload-label">
        <div class="upload-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
        </div>
        <p class="upload-text" >
            <strong>Click to upload</strong> or drag and drop  <br>
            <small>PNG, JPG or GIF (max. 2MB)</small>
        </p>
        <input type="file" name="<?php echo esc_attr($field_name) ?>" id="<?php echo esc_attr($field_name) ?>" accept="image/*" hidden>
    </label>
    
    <div class="image-preview hidden">
        <img src="" alt="Preview">
        <button type="button" class="remove-img">x</button>
    </div>
</div>

<style>
    .dedu-upload-container {
        transform: translateY(80px);
        margin: 0 auto;
        margin-top:-80px ;
        border-radius: 50%;
        width: 200px;
        height: 200px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s ease;
        background: #f9fafb;
        cursor: pointer;
        grid-row: span 3;
    }

    .image-upload {
        border: 2px dashed #d1d5db;
        padding: 40px;
    }

    .dedu-upload-container:hover, 
    .dedu-upload-container.drag-over {
        border-color: #4f46e5; /* Modern Indigo */
        background: #f5f3ff;
    }
    .dedu-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
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
        text-align: center;
    }
    .upload-text strong { color: #4f46e5; }
    .upload-text span { display: block; color: #9ca3af; margin-top: 4px; }

    /* Preview Styling */
    .image-preview {
        position: relative;
        width: 100%;
        height: 100%;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview img {
        /* margin-top: -120ox; */
        
        width: 100%;
        height: 100%;
        /* border-radius: 50%; */
        object-fit: cover;
    }

    .hidden { display: none !important; }

    .remove-img {
        position: absolute;
        top: 5px;
        right: 50%;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
    }
</style>