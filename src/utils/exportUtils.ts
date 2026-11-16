import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
import { CertificateData } from '../types/certificate';

export interface ExportOptions {
  format: 'png' | 'pdf' | 'svg' | 'json';
  scale?: number; // 1, 2, or 3
  filename?: string;
}

async function waitForFontsAndImages(element: HTMLElement): Promise<void> {
  if (typeof document !== 'undefined' && 'fonts' in document) {
    try {
      await (document as Document & { fonts: { ready: Promise<unknown> } }).fonts.ready;
    } catch {
      // ignore font readiness failures
    }
  }
  const imgs = Array.from(element.querySelectorAll('img'));
  await Promise.all(
    imgs.map((img) => {
      if (img.complete) return Promise.resolve();
      return new Promise<void>((resolve) => {
        img.addEventListener('load', () => resolve(), { once: true });
        img.addEventListener('error', () => resolve(), { once: true });
        setTimeout(resolve, 2500);
      });
    })
  );
}

function prepareClonedCertificateDom(clonedDoc: Document): void {
  const scaleWrappers = clonedDoc.querySelectorAll('.certificate-scale-wrapper');
  scaleWrappers.forEach((wrapper) => {
    const htmlWrapper = wrapper as HTMLElement;
    htmlWrapper.style.zoom = '1';
    htmlWrapper.style.transform = 'none';
    htmlWrapper.style.width = 'auto';
    htmlWrapper.style.height = 'auto';
    htmlWrapper.style.margin = '0';
    htmlWrapper.style.padding = '0';
  });

  const el = clonedDoc.querySelector('.certificate-root-element') as HTMLElement | null;
  if (el) {
    el.style.zoom = '1';
    el.style.transform = 'none';
    el.style.boxShadow = 'none';
    el.style.margin = '0';
  }

  clonedDoc.querySelectorAll('.no-print, .shimmer-overlay').forEach((node) => {
    (node as HTMLElement).style.display = 'none';
  });
}

function resolveCaptureDimensions(element: HTMLElement, orientation?: 'landscape' | 'portrait') {
  const dataOrient = (element.getAttribute('data-orientation') as 'landscape' | 'portrait' | null) || orientation;
  if (dataOrient === 'portrait') {
    return { width: 794, height: 1123, isLandscape: false };
  }
  if (dataOrient === 'landscape') {
    return { width: 1123, height: 794, isLandscape: true };
  }
  const width = element.offsetWidth || 1123;
  const height = element.offsetHeight || 794;
  return { width, height, isLandscape: width >= height };
}

export async function exportCertificateAsImage(
  element: HTMLElement,
  filename = 'certificate.png',
  scale = 2
): Promise<void> {
  await waitForFontsAndImages(element);
  const { width, height } = resolveCaptureDimensions(element);

  const canvas = await html2canvas(element, {
    scale,
    width,
    height,
    windowWidth: width,
    windowHeight: height,
    scrollX: 0,
    scrollY: 0,
    useCORS: true,
    allowTaint: true,
    backgroundColor: null,
    logging: false,
    imageTimeout: 15000,
    onclone: prepareClonedCertificateDom,
  });

  const link = document.createElement('a');
  link.download = filename.endsWith('.png') ? filename : `${filename}.png`;
  link.href = canvas.toDataURL('image/png');
  link.click();
}

export async function exportCertificateAsPdf(
  element: HTMLElement,
  data: CertificateData,
  filename = 'certificate.pdf'
): Promise<void> {
  await waitForFontsAndImages(element);
  const { width, height, isLandscape } = resolveCaptureDimensions(element, data.orientation);

  const canvas = await html2canvas(element, {
    scale: 2.5,
    width,
    height,
    windowWidth: width,
    windowHeight: height,
    scrollX: 0,
    scrollY: 0,
    useCORS: true,
    allowTaint: true,
    backgroundColor: null,
    logging: false,
    imageTimeout: 15000,
    onclone: prepareClonedCertificateDom,
  });

  const imgData = canvas.toDataURL('image/png');

  // Standard ISO 216 A4 dimensions in mm: 297 x 210 (landscape) or 210 x 297 (portrait)
  const orientation = isLandscape ? 'landscape' : 'portrait';
  const pdf = new jsPDF({
    orientation,
    unit: 'mm',
    format: 'a4',
    compress: true,
  });

  const pdfWidth = isLandscape ? 297 : 210;
  const pdfHeight = isLandscape ? 210 : 297;

  pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight, undefined, 'FAST');
  pdf.save(filename.endsWith('.pdf') ? filename : `${filename}.pdf`);
}

export function exportCertificateAsJson(data: CertificateData, filename = 'certificate-template.json'): void {
  const jsonStr = JSON.stringify(data, null, 2);
  const blob = new Blob([jsonStr], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

export function printCertificate(): void {
  window.print();
}
