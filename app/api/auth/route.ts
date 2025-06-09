import { type NextRequest, NextResponse } from "next/server"

export async function POST(request: NextRequest) {
  try {
    const { clientId, clientSecret, username, password, scope, tokenUrl } = await request.json()

    // Prepare the OAuth 2.0 password grant request
    const tokenParams = new URLSearchParams({
      grant_type: "password",
      client_id: clientId,
      client_secret: clientSecret,
      username: username,
      password: password,
      scope: scope || "",
    })

    const tokenResponse = await fetch(tokenUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body: tokenParams.toString(),
    })

    if (!tokenResponse.ok) {
      const errorText = await tokenResponse.text()
      return NextResponse.json({ error: "Authentication failed", details: errorText }, { status: 401 })
    }

    const tokenData = await tokenResponse.json()

    return NextResponse.json({
      success: true,
      accessToken: tokenData.access_token,
      tokenType: tokenData.token_type,
      expiresIn: tokenData.expires_in,
    })
  } catch (error) {
    console.error("Authentication error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
