export type CitaStatus =
    | 'pendiente'
    | 'confirmada'
    | 'cancelada'
    | 'completada'

export interface Cita {
    id:number
    doctor_id:number
    paciente:string
    fecha:string
    hora:string
    motivo:string
    estado:CitaStatus
    created_at?:string
    updated_at?:string
}

export interface Doctor {
    id:number
    nombre:string
}

export interface CreateCitaPayload {
    doctor_id:number
    paciente:string
    fecha:string
    hora:string
    motivo:string
    estado:CitaStatus
}

export interface UpdateCitaPayload
extends Partial<CreateCitaPayload> {}