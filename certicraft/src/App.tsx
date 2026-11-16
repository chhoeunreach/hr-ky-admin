import React, { useEffect, useState } from 'react';
import { CertificateStudio } from './CertificateStudio';
import { CertificateData } from './types/certificate';
import { HrAdminEmployeeInput } from './utils/hrIntegration';
import {
  LayoutModelSavePayload,
  LayoutModelSaveResult,
} from './components/LayoutModelSaveModal';

declare global {
  interface Window {
    __LARAVEL_CERT_BOOTSTRAP__?: {
      embedded?: boolean;
      locale?: 'en' | 'km';
      csrfToken?: string;
      saveUrl?: string;
      saveTemplateUrl?: string;
      resetTemplateUrl?: string;
      indexUrl?: string;
      templatesUrl?: string;
      initialCertificate?: CertificateData | null;
      selectedEmployee?: HrAdminEmployeeInput | null;
      employees?: HrAdminEmployeeInput[];
      templates?: any[];
    };
  }
}

export default function App() {
  const bootstrap = typeof window !== 'undefined' ? window.__LARAVEL_CERT_BOOTSTRAP__ : undefined;

  const [externalEmployees, setExternalEmployees] = useState<HrAdminEmployeeInput[]>(() => {
    return bootstrap?.employees && bootstrap.employees.length > 0 ? bootstrap.employees : [];
  });

  const [initialData, setInitialData] = useState<CertificateData | undefined>(() => {
    return bootstrap?.initialCertificate || undefined;
  });

  const [selectedEmployee, setSelectedEmployee] = useState<HrAdminEmployeeInput | null>(() => {
    return bootstrap?.selectedEmployee || null;
  });

  // Fetch real employee roster and/or certificate if not bootstrapped
  useEffect(() => {
    const params = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
    const urlEmpId = params?.get('employee_id');
    const urlCertId = params?.get('id');
    const urlTmplId = params?.get('template_id') || params?.get('template');

    // Fetch real employees roster & templates list
    fetch('/certificates/api/list')
      .then((res) => res.json())
      .then((data) => {
        if (data && Array.isArray(data.employees) && data.employees.length > 0) {
          setExternalEmployees(data.employees);

          // If employee_id is in URL and none selected yet, match from real roster
          if (urlEmpId && !selectedEmployee) {
            const matched = data.employees.find(
              (emp: HrAdminEmployeeInput) =>
                String(emp.employee_code ?? emp.employeeId ?? emp.id ?? '') === String(urlEmpId) ||
                String(emp.id ?? '') === String(urlEmpId)
            );
            if (matched) {
              setSelectedEmployee(matched);
            }
          }
        }

        // If template_id is in URL and initialData not set, match from template list
        if (urlTmplId && !initialData && Array.isArray(data.templates)) {
          const foundTmpl = data.templates.find(
            (t: any) => String(t.templateId ?? t.id ?? t.templateCode ?? '') === String(urlTmplId)
          );
          if (foundTmpl) {
            setInitialData(foundTmpl);
          }
        }

        // If certificate id is in URL and initialData not set, match from certificate list
        if (urlCertId && !initialData && Array.isArray(data.certificates)) {
          const foundCert = data.certificates.find(
            (c: any) => String(c.credentialId ?? c.id ?? '') === String(urlCertId)
          );
          if (foundCert) {
            setInitialData(foundCert);
          }
        }
      })
      .catch((err) => {
        console.warn('API sync warning:', err);
      });
  }, []);

  const getCsrfToken = () =>
    bootstrap?.csrfToken ||
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
    '';

  const handleSave = async (cert: CertificateData) => {
    const saveUrl = bootstrap?.saveUrl || '/certificates/api/save';
    const saveTemplateUrl = bootstrap?.saveTemplateUrl || '/certificates/api/template/save';
    const csrfToken = getCsrfToken();
    // Only keep an existing layout model in sync automatically. New layout models
    // are created explicitly from the “Save as វិញ្ញាបនបត្រ គំរូ” action.
    const existingTemplateCode =
      (cert as any)?.templateCode || (initialData as any)?.templateCode;

    try {
      // 1. Save certificate record
      const resp = await fetch(saveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(cert),
      });

      if (resp.ok) {
        const json = await resp.json();
        console.log('Certificate saved to real database:', json);
      }

      // 2. Update the opened Layout Model / វិញ្ញាបនបត្រ គំរូ
      if (existingTemplateCode) {
        try {
          await fetch(saveTemplateUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
              ...cert,
              templateCode: existingTemplateCode,
              category: cert.category || 'leader',
              layoutTemplate: cert.layoutTemplate,
              languageMode: cert.languageMode || 'bilingual',
              theme: cert.theme,
            }),
          });
          console.log('Layout Model synchronized to database templates');
        } catch (tmplErr) {
          console.warn('Template sync warning:', tmplErr);
        }
      }
    } catch (e) {
      console.warn('Saved offline in localStorage:', e);
    }
  };

  const handleSaveLayoutModel = async (
    payload: LayoutModelSavePayload
  ): Promise<LayoutModelSaveResult> => {
    const saveTemplateUrl = bootstrap?.saveTemplateUrl || '/certificates/api/template/save';
    const csrfToken = getCsrfToken();

    try {
      const resp = await fetch(saveTemplateUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          ...payload.certificate,
          templateId: (payload.certificate as any)?.templateId || (initialData as any)?.templateId,
          templateCode: payload.templateCode || undefined,
          templateTitle: payload.templateTitle,
          templateKhmerTitle: payload.templateKhmerTitle,
          title: payload.templateTitle,
          category: payload.category,
          versionTag: payload.versionTag,
          description: payload.description,
          khmerDescription: payload.khmerDescription,
          isDefault: payload.isDefault,
          layoutTemplate: payload.certificate.layoutTemplate,
          languageMode: payload.certificate.languageMode,
          theme: payload.certificate.theme,
        }),
      });

      let json: any = null;
      try {
        json = await resp.json();
      } catch {
        // Non-JSON response
      }

      if (!resp.ok) {
        return {
          success: false,
          message:
            json?.message ||
            `Unable to save layout model (HTTP ${resp.status}).`,
        };
      }

      return {
        success: true,
        message: json?.message,
        template: json?.template,
      };
    } catch (error: any) {
      return {
        success: false,
        message: error?.message || 'Network error while saving layout model.',
      };
    }
  };

  const handleBack = () => {
    const targetUrl = bootstrap?.templatesUrl || bootstrap?.indexUrl || '/admin/certificates/templates';
    window.location.href = targetUrl;
  };

  const getTemplateCodeFromLayout = (layout?: string): string => {
    switch (layout) {
      case 'executive-verify-v2': return 'TMPL-V2-ID';
      case 'executive-navy-ribbon': return 'TMPL-V3-RIBBON';
      case 'classic': return 'TMPL-V4-ROYAL';
      case 'executive-medal-ribbon': return 'TMPL-V5-MEDAL';
      case 'executive-imperial-sash': return 'TMPL-V6-SASH';
      case 'executive-verify':
      default:
        return 'TMPL-V1-EXECUTIVE';
    }
  };

  const handleSaveCurrentTemplate = async (
    cert: CertificateData
  ): Promise<{ success: boolean; message?: string }> => {
    const saveTemplateUrl = bootstrap?.saveTemplateUrl || '/certificates/api/template/save';
    const csrfToken = getCsrfToken();
    const tmplId =
      (cert as any)?.templateId ||
      (initialData as any)?.templateId;
    const tmplCode =
      (cert as any)?.templateCode ||
      (initialData as any)?.templateCode ||
      getTemplateCodeFromLayout(cert.layoutTemplate);

    try {
      const resp = await fetch(saveTemplateUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          ...cert,
          templateId: tmplId,
          templateCode: tmplCode,
          templateTitle: (cert as any)?.templateTitle || cert.certificateTitle,
          templateKhmerTitle: (cert as any)?.templateKhmerTitle || cert.khmerTitle,
          category: cert.category || 'leader',
          layoutTemplate: cert.layoutTemplate,
          languageMode: cert.languageMode || 'bilingual',
          theme: cert.theme,
        }),
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok) {
        return {
          success: false,
          message: json?.message || 'Error saving template settings.',
        };
      }
      return {
        success: true,
        message: json?.message || 'Template settings saved successfully.',
      };
    } catch (err: any) {
      return {
        success: false,
        message: err?.message || 'Network error while saving template.',
      };
    }
  };

  const handleResetTemplate = async (
    templateIdOrCode: string | number
  ): Promise<{ success: boolean; message?: string; template?: CertificateData }> => {
    const resetBaseUrl = bootstrap?.resetTemplateUrl || '/certificates/api/template';
    const csrfToken = getCsrfToken();

    try {
      const resp = await fetch(`${resetBaseUrl}/${encodeURIComponent(templateIdOrCode)}/reset`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json?.success) {
        return {
          success: false,
          message: json?.message || 'Failed to reset template to default.',
        };
      }

      const resetPayload = json.studioPayload || json.template;
      if (resetPayload) {
        setInitialData(resetPayload);
      }

      return {
        success: true,
        message: json.message,
        template: resetPayload,
      };
    } catch (err: any) {
      return {
        success: false,
        message: err?.message || 'Network error while resetting template.',
      };
    }
  };

  return (
    <CertificateStudio
      embedded={bootstrap?.embedded !== false}
      defaultLanguage={bootstrap?.locale || 'km'}
      initialData={initialData}
      employee={selectedEmployee}
      externalEmployees={externalEmployees}
      onSaveCertificate={handleSave}
      onSaveLayoutModel={handleSaveLayoutModel}
      onSaveTemplateSettings={handleSaveCurrentTemplate}
      onResetLayoutModel={handleResetTemplate}
      templatesUrl={
        bootstrap?.templatesUrl || bootstrap?.indexUrl || '/admin/certificates/templates'
      }
      onBack={handleBack}
    />
  );
}
