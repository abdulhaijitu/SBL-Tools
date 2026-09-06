const fs = require('fs');
const path = require('path');

// 1. Read the rendered pages from Laravel
const renderedPagesPath = path.join(__dirname, 'storage', 'rendered_pages.json');
if (!fs.existsSync(renderedPagesPath)) {
    console.error('storage/rendered_pages.json not found! Run php render_pages.php first.');
    process.exit(1);
}
const renderedPages = JSON.parse(fs.readFileSync(renderedPagesPath, 'utf8'));

// 2. Read compiled CSS & JS assets from public/build
const manifest = JSON.parse(fs.readFileSync(path.join(__dirname, 'public', 'build', 'manifest.json'), 'utf8'));
const cssFileName = manifest['resources/css/app.css'].file;
const jsFileName = manifest['resources/js/app.js'].file;

const cssContent = fs.readFileSync(path.join(__dirname, 'public', 'build', cssFileName), 'utf8');
const jsContent = fs.readFileSync(path.join(__dirname, 'public', 'build', jsFileName), 'utf8');

// 3. Read logo base64
const logoBase64 = fs.readFileSync(path.join(__dirname, 'storage', 'logo_base64.txt'), 'utf8').trim();

// 4. Clean up and standardize rendered HTML
for (const key of Object.keys(renderedPages)) {
    let html = renderedPages[key];

    // Strip any hardcoded localhost URL (http://localhost or https://localhost)
    html = html.replace(/https?:\/\/localhost\/?/g, '/');

    // Standardize image paths to /images/sbl-logo.png
    html = html.replace(/src="[^"]*sbl-logo\.(webp|png)"/g, 'src="/images/sbl-logo.png"');
    html = html.replace(/href="[^"]*sbl-logo\.(webp|png)"/g, 'href="/images/sbl-logo.png"');
    html = html.replace(/href="[^"]*favicon\.(png|ico)"/g, 'href="/favicon.png"');
    html = html.replace(/href="[^"]*apple-touch-icon\.png"/g, 'href="/apple-touch-icon.png"');

    // Ensure asset script and preload tags are relative
    html = html.replace(new RegExp('http:\\/\\/localhost\\/build\\/' + cssFileName, 'g'), '/build/' + cssFileName);
    html = html.replace(new RegExp('http:\\/\\/localhost\\/build\\/' + jsFileName, 'g'), '/build/' + jsFileName);

    renderedPages[key] = html;
}

// 5. Generate worker.js code
const parts = [
  '// SBL Growth Manager - Cloudflare Worker Edge Application',
  '// Serves the exact, 100% pixel-perfect compiled Laravel Blade views and assets on the edge.',
  '',
  'const SBL_LOGO_BASE64 = ' + JSON.stringify(logoBase64) + ';',
  'const CSS_CONTENT = ' + JSON.stringify(cssContent) + ';',
  'const JS_CONTENT = ' + JSON.stringify(jsContent) + ';',
  'const CSS_PATH = ' + JSON.stringify('/build/' + cssFileName) + ';',
  'const JS_PATH = ' + JSON.stringify('/build/' + jsFileName) + ';',
  '',
  'const PAGES = ' + JSON.stringify(renderedPages) + ';',
  '',
  'export default {',
  '  async fetch(request, env, ctx) {',
  '    const url = new URL(request.url);',
  '    const path = url.pathname;',
  '',
  '    // 1. Static Compiled CSS Assets',
  '    if (path.endsWith(".css") || path === CSS_PATH || path === "/build/assets/app.css") {',
  '      return new Response(CSS_CONTENT, {',
  '        headers: {',
  '          "Content-Type": "text/css; charset=utf-8",',
  '          "Cache-Control": "public, max-age=31536000, immutable"',
  '        }',
  '      });',
  '    }',
  '',
  '    // 2. Static Compiled JS Assets',
  '    if (path.endsWith(".js") || path === JS_PATH || path === "/build/assets/app.js") {',
  '      return new Response(JS_CONTENT, {',
  '        headers: {',
  '          "Content-Type": "application/javascript; charset=utf-8",',
  '          "Cache-Control": "public, max-age=31536000, immutable"',
  '        }',
  '      });',
  '    }',
  '',
  '    // 3. Images & Favicons',
  '    if (path.endsWith(".png") || path.endsWith(".ico") || path.endsWith(".webp") || path.includes("sbl-logo") || path.includes("favicon") || path.includes("apple-touch-icon")) {',
  '      const binaryString = atob(SBL_LOGO_BASE64);',
  '      const len = binaryString.length;',
  '      const bytes = new Uint8Array(len);',
  '      for (let i = 0; i < len; i++) {',
  '        bytes[i] = binaryString.charCodeAt(i);',
  '      }',
  '      return new Response(bytes.buffer, {',
  '        headers: {',
  '          "Content-Type": "image/png",',
  '          "Cache-Control": "public, max-age=31536000, immutable"',
  '        }',
  '      });',
  '    }',
  '',
  '    if (path === "/ping") {',
  '      return new Response("pong", { status: 200 });',
  '    }',
  '',
  '    // 4. Handle POST, PUT, PATCH, DELETE form actions gracefully on edge',
  '    if (request.method === "POST" || request.method === "PUT" || request.method === "PATCH" || request.method === "DELETE") {',
  '      if (path.startsWith("/leads")) {',
  '        return Response.redirect(new URL("/leads", request.url), 302);',
  '      }',
  '      if (path === "/tasks" || path.startsWith("/tasks/")) {',
  '        return Response.redirect(new URL("/tasks", request.url), 302);',
  '      }',
  '      if (path === "/presentations") {',
  '        return Response.redirect(new URL("/presentations", request.url), 302);',
  '      }',
  '      if (path === "/binary" || path.startsWith("/binary")) {',
  '        return Response.redirect(new URL("/binary", request.url), 302);',
  '      }',
  '      return Response.redirect(new URL(path, request.url), 302);',
  '    }',
  '',
  '    // 5. Exact Laravel Blade Routes',
  '    let html = null;',
  '',
  '    if (path === "/" || path === "/dashboard") {',
  '      html = PAGES.dashboard;',
  '    } else if (path === "/leads/create") {',
  '      html = PAGES.leads_create;',
  '    } else if (path.match(/^\\/leads\\/\\d+\\/edit$/)) {',
  '      html = PAGES.leads_edit || PAGES.leads;',
  '    } else if (path.match(/^\\/leads\\/\\d+$/)) {',
  '      html = PAGES.leads_show || PAGES.leads;',
  '    } else if (path === "/leads") {',
  '      const viewMode = url.searchParams.get("view");',
  '      html = viewMode === "kanban" ? PAGES.kanban : PAGES.leads;',
  '    } else if (path === "/presentations") {',
  '      html = PAGES.presentations;',
  '    } else if (path === "/toolkit") {',
  '      html = PAGES.toolkit;',
  '    } else if (path === "/tasks") {',
  '      html = PAGES.tasks;',
  '    } else if (path === "/reports") {',
  '      html = PAGES.reports;',
  '    } else if (path === "/marketing/content-calendar") {',
  '      html = PAGES.calendar;',
  '    } else if (path === "/users") {',
  '      html = PAGES.users;',
  '    } else if (path === "/roles" || path.startsWith("/roles/")) {',
  '      html = PAGES.roles;',
  '    } else if (path === "/ecosystem") {',
  '      html = PAGES.ecosystem;',
  '    } else if (path === "/contacts") {',
  '      html = PAGES.contacts;',
  '    } else if (path === "/binary" || path.startsWith("/binary")) {',
  '      html = PAGES.binary;',
  '    } else {',
  '      // Fallback: Dashboard',
  '      html = PAGES.dashboard;',
  '    }',
  '',
  '    // 5. Inject full compiled Tailwind CSS directly into <head> for zero-latency, unbreakable rendering',
  '    let responseHtml = html;',
  '    if (responseHtml && responseHtml.includes("</head>")) {',
  '      responseHtml = responseHtml.replace("</head>", () => "<style id=\\"sbl-edge-styles\\">\\n" + CSS_CONTENT + "\\n</style>\\n</head>");',
  '    }',
  '',
  '    return new Response(responseHtml, {',
  '      headers: {',
  '        "Content-Type": "text/html; charset=utf-8",',
  '        "Cache-Control": "public, max-age=0, must-revalidate",',
  '        "X-Powered-By": "Cloudflare Workers Edge (Laravel Pixel-Perfect Edition)"',
  '      }',
  '    });',
  '  }',
  '};',
  ''
];

fs.writeFileSync(path.join(__dirname, 'worker.js'), parts.join('\n'), 'utf8');
console.log('worker.js generated successfully! Size: ' + fs.statSync(path.join(__dirname, 'worker.js')).size + ' bytes');
