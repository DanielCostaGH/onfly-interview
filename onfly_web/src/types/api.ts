export type UserRole = 'user' | 'admin';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  created_at: string;
}

export interface Airport {
  id: number;
  iata_code: string;
  name: string;
  city: string;
  state: string;
  country: string;
}

export type TravelStatus = 'requested' | 'approved' | 'canceled';

export interface TravelRequest {
  id: number;
  code: string;
  user_id: number;
  destination_airport_id: number;
  status: TravelStatus;
  departure_date: string;
  return_date: string;
  created_at: string;
  updated_at: string;
  user?: User;
  destination_airport?: Airport;
}

export interface NotificationData {
  travel_request_id?: number;
  code?: string;
  from_status?: TravelStatus;
  to_status?: TravelStatus;
  destination?: {
    city: string;
    state: string;
    iata_code: string;
  };
  departure_date?: string;
  return_date?: string;
}

export interface NotificationItem {
  id: string;
  type: string;
  data: NotificationData;
  read_at: string | null;
  created_at: string;
}

export interface LoginResponse {
  token: string;
  user: User;
}
