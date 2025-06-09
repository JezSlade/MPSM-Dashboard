"use client"

import { useState, useEffect } from "react"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"

const roles = ["Developer", "Admin", "Dealer", "Service", "Sales", "Accounting"]

interface ApiConfig {
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

interface DatabaseConfig {
  host: string
  name: string
  user: string
  password: string
}

export default function Home() {
  const [apiConfig, setApiConfig] = useState<ApiConfig>({
    clientId: "",
    clientSecret: "",
    username: "",
    password: "",
    baseUrl: "https://api.abassetmanagement.com/api3",
    scope: "",
    tokenUrl: "https://api.abassetmanagement.com/api3/token",
    dealerCode: "",
    dealerId: "",
  })

  const [dbConfig, setDbConfig] = useState<DatabaseConfig>({
    host: "",
    name: "",
    user: "",
    password: "",
  })

  const [endpoints, setEndpoints] = useState({})
  const [loading, setLoading] = useState(false)
  const [isConfigured, setIsConfigured] = useState(false)

  useEffect(() => {
    // Load configuration from localStorage
    const storedApiConfig = localStorage.getItem("apiConfig")
    const storedDbConfig = localStorage.getItem("dbConfig")

    if (storedApiConfig && storedDbConfig) {
      setApiConfig(JSON.parse(storedApiConfig))
      setDbConfig(JSON.parse(storedDbConfig))
      setIsConfigured(true)
      fetchEndpoints()
    }
  }, [])

  const fetchEndpoints = async () => {
    setLoading(true)
    try {
      // Mock data for demonstration - replace with actual API call
      const mockEndpoints = {
        Developer: ["/ApiClient/List", "/ApiClient/Create", "/ApiClient/Update", "/ApiClient/Delete"],
        Admin: ["/Account/GetProfile", "/Account/Logout", "/Explorer/Hostname/Update", "/Explorer/WorkingDays/Update"],
        Dealer: ["/Customer/GetCustomers", "/Device/List", "/Device/GetDetailedInformations"],
        Service: ["/Device/JobHistory/List", "/Device/SupplyHistory/List", "/SupplyAlert/List"],
        Sales: ["/Customer/GetCustomers", "/Device/List", "/Reports/Sales"],
        Accounting: ["/SupplyAlert/Export", "/Reports/Billing", "/Customer/GetCustomers"],
      }
      setEndpoints(mockEndpoints)
    } catch (error) {
      console.error("Error fetching endpoints:", error)
    } finally {
      setLoading(false)
    }
  }

  const handleSaveConfig = () => {
    localStorage.setItem("apiConfig", JSON.stringify(apiConfig))
    localStorage.setItem("dbConfig", JSON.stringify(dbConfig))
    setIsConfigured(true)
    fetchEndpoints()
  }

  const updateApiConfig = (field: keyof ApiConfig, value: string) => {
    setApiConfig((prev) => ({ ...prev, [field]: value }))
  }

  const updateDbConfig = (field: keyof DatabaseConfig, value: string) => {
    setDbConfig((prev) => ({ ...prev, [field]: value }))
  }

  return (
    <div className="container mx-auto p-4 max-w-6xl">
      <div className="mb-6">
        <h1 className="text-3xl font-bold mb-2">MPS Monitor API Interface</h1>
        <p className="text-muted-foreground">Configure your API and database settings to get started</p>
      </div>

      {!isConfigured && (
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Initial Setup Required</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* API Configuration */}
            <div>
              <h3 className="text-lg font-semibold mb-4">API Configuration</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="clientId">Client ID</Label>
                  <Input
                    id="clientId"
                    type="text"
                    value={apiConfig.clientId}
                    onChange={(e) => updateApiConfig("clientId", e.target.value)}
                    placeholder="Enter client ID"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="clientSecret">Client Secret</Label>
                  <Input
                    id="clientSecret"
                    type="password"
                    value={apiConfig.clientSecret}
                    onChange={(e) => updateApiConfig("clientSecret", e.target.value)}
                    placeholder="Enter client secret"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="username">App Username</Label>
                  <Input
                    id="username"
                    type="text"
                    value={apiConfig.username}
                    onChange={(e) => updateApiConfig("username", e.target.value)}
                    placeholder="Enter app username"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="password">App Password</Label>
                  <Input
                    id="password"
                    type="password"
                    value={apiConfig.password}
                    onChange={(e) => updateApiConfig("password", e.target.value)}
                    placeholder="Enter app password"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="baseUrl">Base URL</Label>
                  <Input
                    id="baseUrl"
                    type="text"
                    value={apiConfig.baseUrl}
                    onChange={(e) => updateApiConfig("baseUrl", e.target.value)}
                    placeholder="https://api.abassetmanagement.com/api3"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="scope">Scope</Label>
                  <Input
                    id="scope"
                    type="text"
                    value={apiConfig.scope}
                    onChange={(e) => updateApiConfig("scope", e.target.value)}
                    placeholder="Enter scope"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="tokenUrl">Token URL</Label>
                  <Input
                    id="tokenUrl"
                    type="text"
                    value={apiConfig.tokenUrl}
                    onChange={(e) => updateApiConfig("tokenUrl", e.target.value)}
                    placeholder="https://api.abassetmanagement.com/api3/token"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dealerCode">Dealer Code</Label>
                  <Input
                    id="dealerCode"
                    type="text"
                    value={apiConfig.dealerCode}
                    onChange={(e) => updateApiConfig("dealerCode", e.target.value)}
                    placeholder="e.g., NY06AGDWUQ"
                  />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label htmlFor="dealerId">Dealer ID</Label>
                  <Input
                    id="dealerId"
                    type="text"
                    value={apiConfig.dealerId}
                    onChange={(e) => updateApiConfig("dealerId", e.target.value)}
                    placeholder="e.g., SZ13qRwU5GtFLj0i_CbEgQ2"
                  />
                </div>
              </div>
            </div>

            <Separator />

            {/* Database Configuration */}
            <div>
              <h3 className="text-lg font-semibold mb-4">Database Configuration</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="dbHost">Database Host</Label>
                  <Input
                    id="dbHost"
                    type="text"
                    value={dbConfig.host}
                    onChange={(e) => updateDbConfig("host", e.target.value)}
                    placeholder="localhost or IP address"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dbName">Database Name</Label>
                  <Input
                    id="dbName"
                    type="text"
                    value={dbConfig.name}
                    onChange={(e) => updateDbConfig("name", e.target.value)}
                    placeholder="Database name"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dbUser">Database User</Label>
                  <Input
                    id="dbUser"
                    type="text"
                    value={dbConfig.user}
                    onChange={(e) => updateDbConfig("user", e.target.value)}
                    placeholder="Database username"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="dbPassword">Database Password</Label>
                  <Input
                    id="dbPassword"
                    type="password"
                    value={dbConfig.password}
                    onChange={(e) => updateDbConfig("password", e.target.value)}
                    placeholder="Database password"
                  />
                </div>
              </div>
            </div>

            <Button onClick={handleSaveConfig} disabled={loading} className="w-full">
              {loading ? "Connecting..." : "Save Configuration & Connect"}
            </Button>
          </CardContent>
        </Card>
      )}

      {isConfigured && (
        <>
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-2xl font-semibold">API Endpoints by Role</h2>
            <Button variant="outline" onClick={() => setIsConfigured(false)}>
              Reconfigure
            </Button>
          </div>

          <Tabs defaultValue="Developer" className="w-full">
            <TabsList className="grid w-full grid-cols-6">
              {roles.map((role) => (
                <TabsTrigger key={role} value={role} className="text-xs">
                  {role}
                </TabsTrigger>
              ))}
            </TabsList>
            {roles.map((role) => (
              <TabsContent key={role} value={role}>
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      {role} Endpoints
                      <span className="text-sm font-normal text-muted-foreground">
                        ({endpoints[role]?.length || 0} endpoints)
                      </span>
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2">
                      {endpoints[role]?.map((endpoint, index) => (
                        <div
                          key={index}
                          className="p-3 border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors"
                        >
                          <code className="text-sm font-mono">{endpoint}</code>
                        </div>
                      )) || <p className="text-muted-foreground">No endpoints available for this role.</p>}
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>
            ))}
          </Tabs>
        </>
      )}
    </div>
  )
}
