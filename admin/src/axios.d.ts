import {
  AxiosAdapter,
  AxiosBasicCredentials, AxiosHeaders,
  AxiosProgressEvent, AxiosProxyConfig,
  AxiosRequestTransformer,
  AxiosResponseTransformer,
  CancelToken, CustomParamsSerializer, FormSerializerOptions, GenericAbortSignal,
  Method, ParamsSerializerOptions,
  ResponseType,
  TransitionalOptions
} from 'axios';

type MethodsHeaders = Partial<{
  [Key in Method as Lowercase<Key>]: AxiosHeaders;
} & { common: AxiosHeaders }>;
type Milliseconds = number;
type MaxUploadRate = number;
type MaxDownloadRate = number;
type AxiosAdapterName = 'xhr' | 'http' | string;
type AxiosAdapterConfig = AxiosAdapter | AxiosAdapterName;

declare module 'axios' {
  export interface AxiosRequestConfig<D = any> {
    url?: string;
    method?: Method | string;
    baseURL?: string;
    transformRequest?: AxiosRequestTransformer | AxiosRequestTransformer[];
    transformResponse?: AxiosResponseTransformer | AxiosResponseTransformer[];
    headers?: (RawAxiosRequestHeaders & MethodsHeaders) | AxiosHeaders;
    params?: any;
    paramsSerializer?: ParamsSerializerOptions | CustomParamsSerializer;
    data?: D;
    timeout?: Milliseconds;
    timeoutErrorMessage?: string;
    withCredentials?: boolean;
    adapter?: AxiosAdapterConfig | AxiosAdapterConfig[];
    auth?: AxiosBasicCredentials;
    responseType?: ResponseType;
    responseEncoding?: responseEncoding | string;
    xsrfCookieName?: string;
    xsrfHeaderName?: string;
    onUploadProgress?: (progressEvent: AxiosProgressEvent) => void;
    onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void;
    maxContentLength?: number;
    validateStatus?: ((status: number) => boolean) | null;
    maxBodyLength?: number;
    maxRedirects?: number;
    maxRate?: number | [MaxUploadRate, MaxDownloadRate];
    beforeRedirect?: (options: Record<string, any>, responseDetails: { headers: Record<string, string> }) => void;
    socketPath?: string | null;
    transport?: any;
    httpAgent?: any;
    httpsAgent?: any;
    proxy?: AxiosProxyConfig | false;
    cancelToken?: CancelToken;
    decompress?: boolean;
    transitional?: TransitionalOptions;
    signal?: GenericAbortSignal;
    insecureHTTPParser?: boolean;
    env?: {
      FormData?: new (...args: any[]) => object;
    };
    formSerializer?: FormSerializerOptions;
    family?: 4 | 6 | undefined;
    lookup?: ((hostname: string, options: object, cb: (err: Error | null, address: string, family: number) => void) => void) |
      ((hostname: string, options: object) => Promise<[address: string, family: number] | string>);

    // Custom
    retry?: boolean;
    showMessage?: boolean;
    uniqId?: string | number;
    skipInterceptor?: boolean;
  }
}
