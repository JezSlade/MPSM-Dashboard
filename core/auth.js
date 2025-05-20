/**
 * v1.2.1 [Export named getToken()]
 */
import debug from './debug.js';

const CLIENT_ID     = '9AT9j4UoU2BgLEqmiYCz';
const CLIENT_SECRET = '9gTbAKBCZe1ftYQbLbq9';
const USERNAME      = 'dashboard';
const PASSWORD      = 'd@$hpa$$2024';
const SCOPE         = 'account';
const URL           = 'https://api.abassetmanagement.com/api3/token';

let token = null, expiry = 0;

async function fetchNew() {
  debug.log('Auth: fetching token');
  const body = new URLSearchParams({ client_id:CLIENT_ID,client_secret:CLIENT_SECRET,grant_type:'password',username:USERNAME,password:PASSWORD,scope:SCOPE });
  const res  = await fetch(URL, { method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString() });
  if(!res.ok) throw new Error(`Token ${res.status}`);
  const {access_token,expires_in} = await res.json();
  token = access_token; expiry = Date.now() + (expires_in*1000)-60000;
  debug.log('Auth: token acquired');
  return token;
}

export async function getToken() {
  if (token && Date.now()<expiry) { debug.log('Auth: using cached'); return token }
  return fetchNew();
}
export default { getToken };
