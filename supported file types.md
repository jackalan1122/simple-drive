# Supported File Types

## Overview

Simple Drive supports uploading any file type up to 500MB. However, only certain file types can be previewed directly in the browser without downloading.

## File Categories & Icons

### 🎥 Video Files
**Can Preview:** ✅ Yes (with player controls and seeking)

**Supported Formats:**
- MP4 (`.mp4`, `.m4v`) - Best compatibility
- WebM (`.webm`)
- OGG (`.ogg`)
- MOV (`.mov`) - QuickTime
- AVI (`.avi`)
- MKV (`.mkv`)
- FLV (`.flv`)
- WMV (`.wmv`)

**Features:**
- Play/pause controls
- Volume control
- Fullscreen mode
- Seek/skip through video
- Stream without downloading entire file

**Best Format Recommendation:** MP4 with H.264 video codec for maximum browser compatibility.

---

### 🖼️ Image Files
**Can Preview:** ✅ Yes (with zoom controls)

**Supported Formats:**
- JPEG (`.jpg`, `.jpeg`)
- PNG (`.png`)
- GIF (`.gif`) - Including animated GIFs
- BMP (`.bmp`)
- WebP (`.webp`)
- SVG (`.svg`) - Vector graphics
- ICO (`.ico`) - Icons

**Features:**
- Full-size preview
- Zoom in/out controls
- Reset zoom
- Responsive display
- No quality loss

**Best Format Recommendations:**
- Photos: JPEG or WebP
- Graphics with transparency: PNG
- Animations: GIF or WebP
- Logos: SVG (scalable)

---

### 🎵 Audio Files
**Can Preview:** ✅ Yes (with audio player)

**Supported Formats:**
- MP3 (`.mp3`) - Best compatibility
- WAV (`.wav`)
- OGG (`.ogg`)
- AAC (`.aac`, `.m4a`)
- FLAC (`.flac`) - Lossless
- WMA (`.wma`)

**Features:**
- Play/pause controls
- Volume control
- Progress bar
- Time display
- Stream without downloading

**Best Format Recommendation:** MP3 for maximum browser compatibility.

---

### 📕 PDF Files
**Can Preview:** ✅ Yes (embedded viewer)

**Supported Formats:**
- PDF (`.pdf`)

**Features:**
- Full document preview
- Zoom controls (browser native)
- Page navigation
- Print option (browser native)
- Search within document (browser native)

**Note:** Preview requires browser with PDF support (all modern browsers).

---

### 📝 Text Files
**Can Preview:** ✅ Yes (code-formatted view)

**Supported Formats:**
- Plain Text (`.txt`)
- Markdown (`.md`)
- Log Files (`.log`)
- JSON (`.json`)
- XML (`.xml`)
- CSV (`.csv`)
- HTML (`.html`)
- CSS (`.css`)
- JavaScript (`.js`)
- PHP (`.php`)
- Python (`.py`)
- Java (`.java`)
- C/C++ (`.c`, `.cpp`)

**Features:**
- Syntax preserved
- Monospace font
- Scrollable view
- Line wrapping
- Copy-friendly format

**Best For:** Configuration files, code files, logs, data files

---

### 📄 Document Files
**Can Preview:** ❌ No (download only)

**Supported Formats:**
- Microsoft Word (`.doc`, `.docx`)
- Microsoft Excel (`.xls`, `.xlsx`)
- Microsoft PowerPoint (`.ppt`, `.pptx`)

**To View:** Download and open with appropriate application (Microsoft Office, LibreOffice, Google Docs, etc.)

---

### 📦 Archive Files
**Can Preview:** ❌ No (download only)

**Supported Formats:**
- ZIP (`.zip`)
- RAR (`.rar`)
- 7-Zip (`.7z`)
- TAR (`.tar`)
- GZIP (`.gz`)

**To Extract:** Download and use extraction software (WinRAR, 7-Zip, The Unarchiver, etc.)

---

## Browser Compatibility

### Video Formats

| Format | Chrome | Firefox | Safari | Edge |
|--------|--------|---------|--------|------|
| MP4    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| WebM   | ✅ Yes | ✅ Yes  | ❌ No  | ✅ Yes |
| OGG    | ✅ Yes | ✅ Yes  | ❌ No  | ❌ No  |
| MOV    | ⚠️ Partial | ❌ No | ✅ Yes | ⚠️ Partial |

### Audio Formats

| Format | Chrome | Firefox | Safari | Edge |
|--------|--------|---------|--------|------|
| MP3    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| WAV    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| OGG    | ✅ Yes | ✅ Yes  | ❌ No  | ❌ No  |
| AAC    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |

### Image Formats

| Format | Chrome | Firefox | Safari | Edge |
|--------|--------|---------|--------|------|
| JPEG   | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| PNG    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| GIF    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| WebP   | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |
| SVG    | ✅ Yes | ✅ Yes  | ✅ Yes | ✅ Yes |

---

## File Size Limits

- **Maximum File Size:** 500MB per file
- **Total Storage:** Unlimited (database dependent)
- **Upload Speed:** Depends on your internet connection

**Upload Time Examples (500MB file):**
- 10 Mbps: ~7 minutes
- 20 Mbps: ~3.5 minutes
- 50 Mbps: ~1.5 minutes
- 100 Mbps: ~40 seconds

---

## Format Conversion Recommendations

### For Maximum Compatibility

**Videos:**
```
Convert to: MP4 (H.264 video, AAC audio)
Tools: HandBrake, FFmpeg, VLC
```

**Audio:**
```
Convert to: MP3
Tools: Audacity, FFmpeg, online converters
```

**Images:**
```
For photos: JPEG or WebP
For graphics: PNG
For animations: GIF
Tools: Photoshop, GIMP, online converters
```

**Documents:**
```
For universal compatibility: PDF
Tools: Microsoft Office, LibreOffice, online converters
```

---

## FFmpeg Conversion Examples

### Video to MP4
```bash
ffmpeg -i input.avi -c:v libx264 -c:a aac -b:v 5M output.mp4
```

### Audio to MP3
```bash
ffmpeg -i input.wav -codec:a libmp3lame -qscale:a 2 output.mp3
```

### Image to JPEG
```bash
ffmpeg -i input.png output.jpg
```

### Extract Audio from Video
```bash
ffmpeg -i video.mp4 -vn -acodec libmp3lame audio.mp3
```

---

## Tips for Best Performance

### 1. **Choose the Right Format**
- Use MP4 for videos (best compatibility)
- Use MP3 for audio (best compatibility)
- Use JPEG for photos (best size/quality ratio)
- Use PNG for graphics with transparency

### 2. **Optimize File Size**
- Compress videos before upload (HandBrake recommended)
- Optimize images (TinyPNG, ImageOptim)
- Use appropriate quality settings (don't over-compress)

### 3. **For Large Files**
- Upload during off-peak hours
- Use wired connection instead of WiFi
- Close other bandwidth-intensive applications

### 4. **Preview Before Upload**
- Test file playback on your device first
- Ensure file isn't corrupted before uploading
- Check file format is supported

---

## Troubleshooting

### File Won't Preview?

1. **Check Format:** Verify it's a supported preview format
2. **Browser Compatibility:** Try a different browser
3. **File Corruption:** Download and check if file plays locally
4. **Browser Cache:** Clear cache and try again

### Preview Shows Error?

1. **Reload Page:** Browser might have timed out
2. **File Size:** Very large files may take time to load
3. **Browser Console:** Check F12 console for errors
4. **Different Browser:** Try Chrome or Firefox

### Download Works but Preview Doesn't?

- This is normal for some formats (PDF on older browsers)
- Download the file to view it locally
- Update your browser to the latest version

---

## Adding Custom File Type Support

If you need to add preview support for additional file types:

1. **Edit `player.php`:**
   - Add extension to appropriate array
   - Add preview HTML/CSS for new type

2. **Edit `index.php`:**
   - Add extension to icon mapping
   - Add extension to preview button logic

3. **Test thoroughly** in different browsers

---

## Security Notes

- All files are scanned for MIME type
- Files are stored with unique names to prevent conflicts
- Users can only access their own files
- Direct file access is protected

**Production Recommendations:**
- Implement virus scanning for uploaded files
- Restrict certain file types if needed (`.exe`, `.bat`, etc.)
- Monitor storage usage per user
- Implement rate limiting on uploads

---

## Quick Reference

✅ **Can Preview (14 types):**
- Video: MP4, WebM, OGG, MOV, AVI, MKV, FLV, WMV
- Audio: MP3, WAV, OGG, AAC, M4A, FLAC, WMA  
- Image: JPG, PNG, GIF, BMP, WebP, SVG, ICO
- Document: PDF
- Text: TXT, MD, LOG, JSON, XML, CSV, HTML, CSS, JS, PHP, PY, JAVA, C, CPP

❌ **Download Only:**
- Microsoft Office: DOC, DOCX, XLS, XLSX, PPT, PPTX
- Archives: ZIP, RAR, 7Z, TAR, GZ
- Executables: EXE, DMG, APP
- All other file types

📏 **Limits:**
- Maximum file size: 500MB
- Upload speed: Based on your connection
- Storage: Limited only by server disk space