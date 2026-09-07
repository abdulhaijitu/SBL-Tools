const { execSync } = require('child_process');

function run(cmd) {
    console.log(`\n\x1b[36m▶ Running: ${cmd}\x1b[0m`);
    execSync(cmd, { stdio: 'inherit' });
}

try {
    const commitMsg = process.argv.slice(2).join(' ') || `Update ${new Date().toLocaleString('en-US')}`;

    console.log('\x1b[32m🚀 Step 1: Pre-rendering Blade pages...\x1b[0m');
    run('php render_pages.php');

    console.log('\x1b[32m📦 Step 2: Compiling Edge Worker bundle...\x1b[0m');
    run('node build_worker.cjs');

    console.log('\x1b[32m☁️ Step 3: Safely deploying to Cloudflare (D1 Data remains 100% untouched)...\x1b[0m');
    run('npx wrangler deploy');

    console.log('\x1b[32m🐙 Step 4: Staging and committing to Git...\x1b[0m');
    run('git add -A');
    try {
        run(`git commit -m "${commitMsg.replace(/"/g, '\\"')}"`);
    } catch (e) {
        console.log('No new git changes to commit.');
    }

    console.log('\x1b[32m⬆️ Step 5: Pushing to GitHub origin main...\x1b[0m');
    run('git push origin main');

    console.log('\n\x1b[32m✅ SUCCESS! Cloudflare is Live and GitHub is updated. Data in D1 is 100% safe!\x1b[0m\n');
} catch (error) {
    console.error('\n\x1b[31m❌ Error during deploy & push:\x1b[0m', error.message);
    process.exit(1);
}

