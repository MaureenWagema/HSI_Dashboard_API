# Power BI Integration Setup Guide

## Environment Variables

Add these to your `.env` file:

```env
POWERBI_TENANT_ID=your-tenant-id-here
POWERBI_CLIENT_ID=your-client-id-here
POWERBI_CLIENT_SECRET=your-client-secret-here
POWERBI_REPORT_ID=your-report-id-here
POWERBI_GROUP_ID=your-workspace-id-here
```

## Quick Setup Steps

### 1. Get Power BI IDs
- **Workspace ID**: From Power BI URL `https://app.powerbi.com/groups/{WORKSPACE_ID}/...`
- **Report ID**: From report URL `https://app.powerbi.com/groups/{WORKSPACE_ID}/reports/{REPORT_ID}...`

### 2. Create Azure AD App
1. Go to Azure Portal → Azure Active Directory → App registrations
2. Click "New registration"
3. Add Power BI Service permissions:
   - Dataset.Read.All
   - Report.Read.All  
   - Workspace.Read.All
4. Grant admin consent

### 3. Get App Credentials
- **Client ID**: From app registration Overview page
- **Tenant ID**: From app registration Overview page
- **Client Secret**: Create under Certificates & secrets

### 4. Update .env
Replace placeholder values with your actual IDs and secrets.

### 5. Clear Config Cache
```bash
php artisan config:clear
```

## Test the Integration

Access the endpoint: `GET /api/powerbi/embed` (requires authentication)

## Troubleshooting

- **404 Not Found**: Check tenant ID is set correctly
- **400 Bad Request**: Ensure workspace and report IDs are valid
- **401 Unauthorized**: Verify app permissions and client secret
