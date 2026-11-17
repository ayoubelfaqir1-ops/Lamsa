import { APIRequestContext } from '@playwright/test';

export interface AuthSession {
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
  headers: Record<string, string>;
}

/**
 * Authenticate with the seeded buyer account.
 */
export async function loginBuyer(request: APIRequestContext): Promise<AuthSession> {
  const response = await request.post('/api/v1/auth/login', {
    data: {
      email: 'buyer@lamsa.ma',
      password: process.env.ADMIN_PASSWORD || 'password123',
    },
  });

  if (!response.ok()) {
    throw new Error(`Failed to login as buyer: ${response.status()} ${await response.text()}`);
  }

  const json = await response.json();
  const token = json.access_token || json.token || json.data?.token;

  return {
    token,
    user: json.user || json.data?.user,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };
}

/**
 * Authenticate with the seeded artisan account.
 */
export async function loginArtisan(request: APIRequestContext): Promise<AuthSession> {
  const response = await request.post('/api/v1/auth/login', {
    data: {
      email: 'artisan@lamsa.ma',
      password: process.env.ADMIN_PASSWORD || 'password123',
    },
  });

  if (!response.ok()) {
    throw new Error(`Failed to login as artisan: ${response.status()} ${await response.text()}`);
  }

  const json = await response.json();
  const token = json.access_token || json.token || json.data?.token;

  return {
    token,
    user: json.user || json.data?.user,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };
}

/**
 * Authenticate with the seeded admin account.
 */
export async function loginAdmin(request: APIRequestContext): Promise<AuthSession> {
  const response = await request.post('/api/v1/auth/login', {
    data: {
      email: 'admin@lamsa.ma',
      password: process.env.ADMIN_PASSWORD || 'password123',
    },
  });

  if (!response.ok()) {
    throw new Error(`Failed to login as admin: ${response.status()} ${await response.text()}`);
  }

  const json = await response.json();
  const token = json.access_token || json.token || json.data?.token;

  return {
    token,
    user: json.user || json.data?.user,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };
}

