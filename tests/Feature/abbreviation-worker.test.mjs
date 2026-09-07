import test from 'node:test';
import assert from 'node:assert/strict';
import { DatabaseSync } from 'node:sqlite';
import { readFileSync } from 'node:fs';
import worker from '../../worker.js';
const sqlite = new DatabaseSync(':memory:');
sqlite.exec(readFileSync(new URL('../../database/abbreviations_d1.sql', import.meta.url), 'utf8'));
const db = {prepare(sql) { let args = []; return {bind(...values) {args = values; return this;}, async first() {return sqlite.prepare(sql).get(...args);}, async run() {const result = sqlite.prepare(sql).run(...args); return {meta: {last_row_id: Number(result.lastInsertRowid)}};}, async all() {return {results: sqlite.prepare(sql).all(...args)};}};}};
const env = {DB: db, ABBREVIATIONS_ADMIN_PASSWORD: 'test-only-password'};
const call = (path, method, body, extra = {}) => worker.fetch(new Request('https://example.test'+path, {method, headers: {Origin: 'https://example.test', Authorization: 'Basic '+btoa('admin:test-only-password'), 'Content-Type': 'application/json', ...extra}, body: body ? JSON.stringify(body) : undefined}), env, {});
test('Worker CRUD persists changes and rejects duplicate terms', async () => {
 const data = {code: 'TEST', name: "A 'quoted' <term>", category_slug: 'finance', meaning_bn: 'পরীক্ষা', description_bn: 'ব্যবহার'};
 let response = await call('/abbreviations','POST',data); assert.equal(response.status,201); const term = await response.json();
 assert.equal((await call('/abbreviations','POST',data)).status,422);
 response = await call('/abbreviations/'+term.id,'PUT',{...data,name:'Updated'}); assert.equal(response.status,200); assert.equal((await response.json()).name,'Updated');
 assert.equal((await call('/abbreviations/'+term.id,'DELETE')).status,204);
 assert.equal((await call('/abbreviations/'+term.id,'DELETE')).status,404);
});
test('Worker rejects unauthorized and cross-origin writes and invalid input', async () => {
 assert.equal((await call('/abbreviations','POST',{}, {Authorization: ''})).status,401);
 assert.equal((await call('/abbreviations','POST',{}, {Origin:'https://evil.test'})).status,403);
 assert.equal((await call('/abbreviations','POST',{})).status,422);
 assert.equal((await worker.fetch(new Request('https://example.test/abbreviations',{method:'POST'}),{DB:db},{})).status,403);
});
