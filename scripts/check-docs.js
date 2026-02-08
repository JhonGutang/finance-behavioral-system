const fs = require('fs');
const path = require('path');

const ROOT_DIR = process.cwd();
const AGENTS_MD_PATH = path.join(ROOT_DIR, 'AGENTS.MD');
const DOCS_DIR = path.join(ROOT_DIR, 'docs');

const mode = process.argv[2] || 'check'; // 'check' or 'sync'

function log(message, type = 'info') {
    const colors = {
        info: '\x1b[36m%s\x1b[0m',
        success: '\x1b[32m%s\x1b[0m',
        warning: '\x1b[33m%s\x1b[0m',
        error: '\x1b[31m%s\x1b[0m',
    };
    console.log(colors[type] || '%s', message);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function checkDocs() {
    log(`--- Documentation ${mode === 'sync' ? 'Syncing' : 'Check'} ---`, 'info');

    if (!fs.existsSync(AGENTS_MD_PATH)) {
        log('Error: AGENTS.MD not found in root!', 'error');
        return;
    }

    let agentsContent = fs.readFileSync(AGENTS_MD_PATH, 'utf8');
    const docsInDir = fs.readdirSync(DOCS_DIR).filter(f => f.endsWith('.md'));

    // Extract docs listed in AGENTS.MD
    const docSectionMatch = agentsContent.match(/## Documentation([\s\S]*?)(?=##|$)/);
    let listedDocs = [];
    if (docSectionMatch) {
        const docLinks = docSectionMatch[1].match(/\[(.*?)\]/g) || [];
        listedDocs = docLinks.map(link => link.replace(/[\[\]]/g, '')).filter(name => name.endsWith('.md'));
    }

    log(`Found ${listedDocs.length} docs listed in AGENTS.MD`);
    log(`Found ${docsInDir.length} docs in /docs directory`);

    let issues = 0;
    const today = formatDate(new Date());

    // Check 1: Missing in /docs
    listedDocs.forEach(doc => {
        if (!docsInDir.includes(doc)) {
            log(`- Missing: ${doc} is listed in AGENTS.MD but not found in /docs`, 'error');
            issues++;
        }
    });

    // Check 2: Unlisted in AGENTS.MD
    const unlisted = docsInDir.filter(doc => !listedDocs.includes(doc));
    if (unlisted.length > 0) {
        unlisted.forEach(doc => {
            log(`- Unlisted: ${doc} exists in /docs but is not mentioned in AGENTS.MD`, 'warning');
            issues++;
        });

        if (mode === 'sync') {
            log(`  Syncing: Adding unlisted docs to AGENTS.MD...`, 'info');
            let newSection = docSectionMatch[1].trim();
            unlisted.forEach(doc => {
                newSection += `\n- **[${doc}](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/${doc})** - New documentation file`;
            });
            agentsContent = agentsContent.replace(docSectionMatch[1], '\n\n' + newSection + '\n\n');
            fs.writeFileSync(AGENTS_MD_PATH, agentsContent);
            log(`  Updated AGENTS.MD`, 'success');
        }
    }

    // Check 3: Freshness & Format
    docsInDir.forEach(doc => {
        const filePath = path.join(DOCS_DIR, doc);
        let content = fs.readFileSync(filePath, 'utf8');
        const lastUpdatedMatch = content.match(/> \*\*Last Updated:\*\* (\d{4}-\d{2}-\d{2})/i);
        
        if (lastUpdatedMatch) {
            const lastDate = new Date(lastUpdatedMatch[1]);
            const now = new Date();
            const diffDays = Math.ceil((now - lastDate) / (1000 * 60 * 60 * 24));
            
            if (diffDays > 30) {
                log(`- Stale: ${doc} was last updated ${diffDays} days ago (${lastUpdatedMatch[1]})`, 'warning');
                issues++;
                if (mode === 'sync') {
                    log(`  Syncing: Updating ${doc} timestamp to ${today}`, 'info');
                    content = content.replace(lastUpdatedMatch[0], `> **Last Updated:** ${today}`);
                    fs.writeFileSync(filePath, content);
                }
            }
        } else {
            log(`- Format: ${doc} is missing a "Last Updated" field`, 'info');
            issues++;
            if (mode === 'sync') {
                log(`  Syncing: Adding "Last Updated" field to ${doc}`, 'info');
                // Insert after the H1 or at the top
                const h1Match = content.match(/^# .*\n/);
                const updateLine = `\n> **Last Updated:** ${today}\n`;
                if (h1Match) {
                    content = content.replace(h1Match[0], h1Match[0] + updateLine);
                } else {
                    content = updateLine + content;
                }
                fs.writeFileSync(filePath, content);
            }
        }
    });

    if (issues === 0) {
        log('\nAll documentation is synced and well-managed! ✓', 'success');
    } else {
        log(`\n${mode === 'sync' ? 'Synced' : 'Found'} ${issues} issues.`, mode === 'sync' ? 'success' : 'warning');
    }
}

checkDocs();
