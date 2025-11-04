export async function apiRequest(url, method = 'GET', body = null) {
  const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
  if (!apiToken) {
    return Promise.reject(new Error('No API Token'));
  }
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const headers = {
    Accept: 'application/json',
    Authorization: `Bearer ${apiToken}`,
    'X-CSRF-TOKEN': csrfToken || '',
  };
  if (body) headers['Content-Type'] = 'application/json';

  const options = { method, headers };
  if (body) options.body = typeof body === 'string' ? body : JSON.stringify(body);

  const response = await fetch(url, options);

  if (response.status === 401) {
    throw new Error('Unauthorized');
  }
  if (response.status === 204 && method === 'DELETE') {
    return { success: true, message: 'No Content' };
  }

  const contentType = response.headers.get('content-type') || '';
  const text = await response.text();
  if (!contentType.includes('application/json')) {
    throw new Error(`Server error: ${response.status}. ${text.substring(0, 150)}`);
  }
  const data = JSON.parse(text || '{}');
  if (!response.ok) {
    if (response.status === 422 && data.errors) {
      const err = new Error(data.message || 'Validation failed');
      err.status = 422;
      err.errors = data.errors;
      throw err;
    }
    throw new Error(data.message || `HTTP error! status: ${response.status}`);
  }
  return data;
}

