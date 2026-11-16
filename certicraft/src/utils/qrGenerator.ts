import QRCode from 'qrcode';

/**
 * Resolve verification URL so that it always points to the active application's real verify route:
 * `${window.location.origin}/certificates/verify/${credentialId}`
 */
export function resolveVerificationUrl(url?: string, credentialId?: string): string {
  const bootstrap = typeof window !== 'undefined' ? (window as any).__LARAVEL_CERT_BOOTSTRAP__ : undefined;
  const origin = typeof window !== 'undefined' && window.location?.origin ? window.location.origin : '';
  const baseUrl = bootstrap?.verifyBaseUrl || (origin ? `${origin}/certificates/verify` : '/certificates/verify');
  const id = credentialId || 'KY-PS-VFY-2026-002';
  const defaultVerifyUrl = `${baseUrl}/${id}`;

  if (!url || typeof url !== 'string' || !url.trim()) {
    return defaultVerifyUrl;
  }

  const trimmed = url.trim();

  // If pointing to dummy legacy/external verification domains
  if (
    trimmed.includes('verify.kneayoeng.org') ||
    trimmed.includes('verify.academics.org') ||
    trimmed.includes('verify-credential.org')
  ) {
    const parts = trimmed.split('/');
    const code = parts[parts.length - 1] || id;
    return `${baseUrl}/${code}`;
  }

  // If it's a relative path starting with /
  if (trimmed.startsWith('/')) {
    return origin ? `${origin}${trimmed}` : trimmed;
  }

  return trimmed;
}

export async function generateQrDataUrl(text: string, color = '#092653', bgColor = '#ffffff'): Promise<string> {
  try {
    const resolvedText = resolveVerificationUrl(text);
    const url = await QRCode.toDataURL(resolvedText, {
      width: 220,
      margin: 1,
      color: {
        dark: color,
        light: bgColor,
      },
      errorCorrectionLevel: 'H',
    });
    return url;
  } catch (err) {
    console.error('Failed to generate QR code:', err);
    return '';
  }
}

export async function generateQrSvgString(text: string, color = '#092653'): Promise<string> {
  try {
    const resolvedText = resolveVerificationUrl(text);
    const svg = await QRCode.toString(resolvedText, {
      type: 'svg',
      margin: 1,
      color: {
        dark: color,
        light: '#00000000',
      },
      errorCorrectionLevel: 'H',
    });
    return svg;
  } catch (err) {
    console.error('Failed to generate QR SVG:', err);
    return '';
  }
}
