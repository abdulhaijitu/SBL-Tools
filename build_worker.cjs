const fs = require("fs");
const path = require("path");

// 1. Read the rendered pages from Laravel
const renderedPagesPath = path.join(
    __dirname,
    "storage",
    "rendered_pages.json",
);
if (!fs.existsSync(renderedPagesPath)) {
    console.error(
        "storage/rendered_pages.json not found! Run php render_pages.php first.",
    );
    process.exit(1);
}
const renderedPages = JSON.parse(fs.readFileSync(renderedPagesPath, "utf8"));

// 2. Read compiled CSS & JS assets from public/build
const manifest = JSON.parse(
    fs.readFileSync(
        path.join(__dirname, "public", "build", "manifest.json"),
        "utf8",
    ),
);
const cssFileName = manifest["resources/css/app.css"].file;
const jsFileName = manifest["resources/js/app.js"].file;

const cssContent = fs.readFileSync(
    path.join(__dirname, "public", "build", cssFileName),
    "utf8",
);
const jsContent = fs.readFileSync(
    path.join(__dirname, "public", "build", jsFileName),
    "utf8",
);
const clientSyncJs = fs.readFileSync(
    path.join(__dirname, "client_sync.js"),
    "utf8",
);

// 3. Read logo base64, package assets & PWA assets
const logoBase64 = fs
    .readFileSync(path.join(__dirname, "storage", "logo_base64.txt"), "utf8")
    .trim();
const qrBase64 = fs.existsSync(path.join(__dirname, "public", "images", "sbl-packages-qr.png"))
    ? fs.readFileSync(path.join(__dirname, "public", "images", "sbl-packages-qr.png")).toString("base64")
    : "";
const sheetBase64 = fs.existsSync(path.join(__dirname, "public", "images", "sbl-packages-sheet.png"))
    ? fs.readFileSync(path.join(__dirname, "public", "images", "sbl-packages-sheet.png")).toString("base64")
    : "";
const manifestContent = fs.readFileSync(
    path.join(__dirname, "public", "manifest.webmanifest"),
    "utf8",
);
const swContent = fs.readFileSync(
    path.join(__dirname, "public", "sw.js"),
    "utf8",
);
const offlineContent = fs.readFileSync(
    path.join(__dirname, "public", "offline.html"),
    "utf8",
);

// 4. Clean up and standardize rendered HTML
for (const key of Object.keys(renderedPages)) {
    let html = renderedPages[key];

    // Strip any hardcoded localhost URL (http://localhost or https://localhost)
    html = html.replace(/https?:\/\/localhost\/?/g, "/");

    // Standardize image paths to /images/sbl-logo.png
    html = html.replace(
        /src="[^"]*sbl-logo\.(webp|png)"/g,
        'src="/images/sbl-logo.png"',
    );
    html = html.replace(
        /href="[^"]*sbl-logo\.(webp|png)"/g,
        'href="/images/sbl-logo.png"',
    );
    html = html.replace(
        /href="[^"]*favicon\.(png|ico)"/g,
        'href="/favicon.png"',
    );
    html = html.replace(
        /href="[^"]*apple-touch-icon\.png"/g,
        'href="/apple-touch-icon.png"',
    );

    // Ensure asset script and preload tags are relative
    html = html.replace(
        new RegExp("http:\\/\\/localhost\\/build\\/" + cssFileName, "g"),
        "/build/" + cssFileName,
    );
    html = html.replace(
        new RegExp("http:\\/\\/localhost\\/build\\/" + jsFileName, "g"),
        "/build/" + jsFileName,
    );

    renderedPages[key] = html;
}

// 5. Generate worker.js code using worker_template.js
const templatePath = path.join(__dirname, "worker_template.js");
let workerContent = fs.readFileSync(templatePath, "utf8");

workerContent = workerContent
    .replace("__SBL_LOGO_BASE64__", () => JSON.stringify(logoBase64))
    .replace("__SBL_PACKAGES_QR_BASE64__", () => JSON.stringify(qrBase64))
    .replace("__SBL_PACKAGES_SHEET_BASE64__", () => JSON.stringify(sheetBase64))
    .replace("__CSS_CONTENT__", () => JSON.stringify(cssContent))
    .replace("__JS_CONTENT__", () => JSON.stringify(jsContent))
    .replace("__CSS_PATH__", () => JSON.stringify("/build/" + cssFileName))
    .replace("__JS_PATH__", () => JSON.stringify("/build/" + jsFileName))
    .replace("__PAGES__", () => JSON.stringify(renderedPages))
    .replace("__CLIENT_SYNC_JS__", () => JSON.stringify(clientSyncJs))
    .replace("__MANIFEST_CONTENT__", () => JSON.stringify(manifestContent))
    .replace("__SW_CONTENT__", () => JSON.stringify(swContent))
    .replace("__OFFLINE_CONTENT__", () => JSON.stringify(offlineContent));

fs.writeFileSync(path.join(__dirname, "worker.js"), workerContent, "utf8");
console.log(
    "worker.js generated successfully! Size: " +
        fs.statSync(path.join(__dirname, "worker.js")).size +
        " bytes",
);
