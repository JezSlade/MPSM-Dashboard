export interface ApiConfig {
  clientId: string
  clientSecret: string
  username: string
  password: string
  baseUrl: string
  scope: string
  tokenUrl: string
  dealerCode: string
  dealerId: string
}

export interface DatabaseConfig {
  host: string
  name: string
  user: string
  password: string
}

export interface AppConfig {
  api: ApiConfig
  database: DatabaseConfig
}

export const defaultApiConfig: ApiConfig = {
  clientId: "",
  clientSecret: "",
  username: "",
  password: "",
  baseUrl: "https://api.abassetmanagement.com/api3",
  scope: "",
  tokenUrl: "https://api.abassetmanagement.com/api3/token",
  dealerCode: "",
  dealerId: "",
}

export const defaultDatabaseConfig: DatabaseConfig = {
  host: "",
  name: "",
  user: "",
  password: "",
}

export const validateApiConfig = (config: ApiConfig): string[] => {
  const errors: string[] = []

  if (!config.clientId) errors.push("Client ID is required")
  if (!config.clientSecret) errors.push("Client Secret is required")
  if (!config.username) errors.push("Username is required")
  if (!config.password) errors.push("Password is required")
  if (!config.baseUrl) errors.push("Base URL is required")
  if (!config.tokenUrl) errors.push("Token URL is required")

  return errors
}

export const validateDatabaseConfig = (config: DatabaseConfig): string[] => {
  const errors: string[] = []

  if (!config.host) errors.push("Database host is required")
  if (!config.name) errors.push("Database name is required")
  if (!config.user) errors.push("Database user is required")
  if (!config.password) errors.push("Database password is required")

  return errors
}
