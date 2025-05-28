async function login(username, password) {
  const res = await fetch('/api/sysop/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  return await res.json();
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const result = await login(username, password);
  if (result.success) {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('main-ui').style.display = 'block';
    loadModules(username);
  } else {
    document.getElementById('login-error').textContent = result.error;
  }
});

async function loadModules(currentUser) {
  const users = await fetch('/core/users.json').then(r => r.json());
  const user = users.users.find(u => u.username === currentUser);
  const permissions = user ? user.permissions : [];

  const modules = await fetch('/core/modules.json').then(r => r.json());
  for (const mod of modules) {
    if (permissions.includes(mod.permission)) {
      const container = document.createElement('div');
      container.id = `module-${mod.id}`;
      document.getElementById('modules-container').appendChild(container);

      const script = document.createElement('script');
      script.type = 'module';
      script.src = mod.script;
      container.appendChild(script);
    }
  }
}
