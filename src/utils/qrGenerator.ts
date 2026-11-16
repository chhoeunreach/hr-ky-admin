import QRCode from 'qrcode';

export async function generateQrDataUrl(text: string, color = '#092653', bgColor = '#ffffff'): Promise<string> {
  try {
    const url = await QRCode.toDataURL(text || 'https://verify-credential.org', {
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
    const svg = await QRCode.toString(text || 'https://verify-credential.org', {
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
