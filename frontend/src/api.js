const API = '/api/notes'

async function request(path = '', options = {}) {
  const response = await fetch(`${API}${path}`, {
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...options.headers,
    },
    ...options,
  })

  if (response.status === 204) {
    return null
  }

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    const details = data.violations
      ? data.violations.map((item) => item.message).join(' ')
      : data.error || `Ошибка ${response.status}`
    throw new Error(details)
  }

  return data
}

export const notesApi = {
  list: (params = {}) => {
    const query = new URLSearchParams()
    if (params.limit != null) {
      query.set('limit', String(params.limit))
    }
    if (params.offset != null) {
      query.set('offset', String(params.offset))
    }
    const suffix = query.toString() ? `?${query}` : ''
    return request(suffix)
  },
  get: (id) => request(`/${id}`),
  create: (payload) => request('', { method: 'POST', body: JSON.stringify(payload) }),
  update: (id, payload) => request(`/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  remove: (id) => request(`/${id}`, { method: 'DELETE' }),
}
